<?php

namespace Hebis\Search\EDS;

use VuFind\Search\EDS\Options;
use VuFind\Search\EDS\QueryAdapter;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\Query;
use VuFindSearch\Query\QueryGroup;

class Params extends \VuFind\Search\EDS\Params
{

    public function __construct(Options $options, \VuFind\Config\PluginManager $configLoader)
    {
        parent::__construct($options, $configLoader);
        $config = $configLoader->get('EDS');
        if (isset($config->LegacyFields)) {
            $this->facetAliases = $config->LegacyFields->toArray();
        }
    }

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
        $output = '';
        //There should only ever be 1 group with EDS queries.
        $all = [];
        foreach ($query->getQueries() as $search) {
            if ($search instanceof QueryGroup) {
                // Process each search group. There should only be 1 with EDS queries
                $groupQueries = $search->getQueries();
                for ($i = 0; $i < count($groupQueries); $i++) {
                    $group = $groupQueries[$i];
                    if ($group instanceof Query) {
                        // Build this group individually as a basic search
                        $queryOperator = $group->getOperator();
                        $op = (null != $queryOperator && 0 != $i) ?
                            $queryOperator . ' ' : '';
                        $all[] = $op
                            . call_user_func($showName, $group->getHandler()) . ':'
                            . '<i>' . $group->getString() . '</i>';
                    } else {
                        throw new \Exception('Unexpected ' . get_class($group));
                    }
                }
            } else {
                throw new \Exception('Unexpected ' . get_class($search));
            }
        }
        $output = '(' . join(' ', $all) . ')';

        return $output;

    }

    /**
     * Does the object already contain the specified filter?
     *
     * @param string $filter A filter string from url : "field:value"
     *
     * @return bool
     */
    public function hasFilter($filter)
    {
        // Extract field and value from URL string:
        list($field, $value) = $this->parseFilter($filter);

        // Check all of the relevant fields for matches:
        if ($alias = $this->getAliasForFacetField($field)) {
            $current = $alias;
        } else {
            $current = $field;
        }
        if (isset($this->filterList[$current])) {
            $value = mb_strtolower($value);
            if (array_search($value, $this->filterList[$current]) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getAliasForFacetField($field)
    {
        return array_search($field, $this->facetAliases);
    }
}