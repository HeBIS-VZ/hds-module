<?php

namespace Hebis\Search\EDS;

use VuFind\Search\EDS\QueryAdapter;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\QueryGroup;

class Params extends \VuFind\Search\EDS\Params
{

    public function getDisplayQueryInputField()
    {
        $query = $this->getQuery();
        if ($query instanceof  \VuFindSearch\Query\Query) {
            return $query->getString();
        }
        return ""; //empty for advanced search
    }

    /**
     * Override for build a string for onscreen display showing the
     *   query used in the search. It will include field level operators instead
     *   of group operators (Since EDS only uses one group.)
     *
     * @return string user friendly version of 'query'
     */
    public function getDisplayQuery()
    {
        // Set up callbacks:
        $showField = [$this->getOptions(), 'getHumanReadableFieldName'];

        // Build display query:
        return self::display($this->getQuery(), $showField);
    }

    public static function display(AbstractQuery $query, $showName)
    {
        // Simple case -- basic query:
        if ($query instanceof  \VuFindSearch\Query\Query) {
            return '(' . call_user_func($showName, $query->getHandler()) . ':'
                . '<i>' . $query->getString() . '</i>)';
        }

        // Complex case -- advanced query:
        return self::displayAdvanced($query, $showName);
    }

    protected static function displayAdvanced(AbstractQuery $query, $showName) {
        // Groups and exclusions.
        $groups = $excludes = [];

        foreach ($query->getQueries() as $search) {
            if ($search instanceof QueryGroup) {
                $thisGroup = [];
                // Process each search group
                foreach ($search->getQueries() as $group) {
                    if ($group instanceof \VuFindSearch\Query\Query) {
                        // Build this group individually as a basic search
                        $thisGroup[]
                            = call_user_func($showName, $group->getHandler()) . ':'
                            . '<i>' . $group->getString() . '</i>';
                    } else {
                        throw new \Exception('Unexpected ' . get_class($group));
                    }
                }
                // Is this an exclusion (NOT) group or a normal group?
                $str = join(
                    ' ' . $search->getOperator()
                    . ' ', $thisGroup
                );
                if ($search->isNegated()) {
                    $excludes[] = $str;
                } else {
                    $groups[] = $str;
                }
            } else {
                throw new \Exception('Unexpected ' . get_class($search));
            }
        }

        // Base 'advanced' query
        $operator = $query->getOperator();
        $output = '(' . join(') ' . $operator . ' (', $groups) . ')';

        // Concatenate exclusion after that
        if (count($excludes) > 0) {
            $output .= ' ' . 'NOT' . ' (('
                . join(') ' . 'OR' . ' (', $excludes)
                . '))';
        }

        return $output;
    }
}