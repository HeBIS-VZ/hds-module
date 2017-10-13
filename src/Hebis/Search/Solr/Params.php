<?php

namespace Hebis\Search\Solr;


use VuFind\Search\QueryAdapter;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Query\QueryGroup;

class Params extends \VuFind\Search\Solr\Params
{

    public function __construct($options, \VuFind\Config\PluginManager $configLoader)
    {
        parent::__construct($options, $configLoader);
    }



    /**
     * Format a single filter for use in getFilterList().
     *
     * @param string $field     Field name
     * @param string $value     Field value
     * @param string $operator  Operator (AND/OR/NOT)
     * @param bool   $translate Should we translate the label?
     *
     * @return array
     */
    protected function formatFilterListEntry($field, $value, $operator, $translate)
    {
        $filter = parent::formatFilterListEntry(
            $field, $value, $operator, $translate
        );

        $hierarchicalFacets = $this->getOptions()->getHierarchicalFacets();
        $hierarchicalFacetSeparators
            = $this->getOptions()->getHierarchicalFacetSeparators();
        $facetHelper = null;
        if (!empty($hierarchicalFacets)) {
            $facetHelper = $this->getServiceLocator()
                ->get('VuFind\HierarchicalFacetHelper');
        }
        // Convert range queries to a language-non-specific format:
        $caseInsensitiveRegex = '/^\(\[(.*) TO (.*)\] OR \[(.*) TO (.*)\]\)$/';
        if (preg_match('/^\[(.*) TO (.*)\]$/', $value, $matches)) {
            // Simple case: [X TO Y]

            for ($i = 1; $i < count($matches); ++$i) {
                $match = $matches[$i];
                if (preg_match("/(\d{4})-(\d{2})-(\d{2})T(\d{2}:\d{2}:\d{2})Z/", $match, $matchesDateFormat)) {
                    $matches[$i] = $matchesDateFormat[1]; // only years
                }
            }

            $filter['displayText'] = $matches[1] . '-' . $matches[2];
        } else if (preg_match($caseInsensitiveRegex, $value, $matches)) {
            // Case insensitive case: [x TO y] OR [X TO Y]; convert
            // only if values in both ranges match up!
            if (strtolower($matches[3]) == strtolower($matches[1])
                && strtolower($matches[4]) == strtolower($matches[2])
            ) {
                $filter['displayText'] = $matches[1] . '-' . $matches[2];
            }
        } else if (in_array($field, $hierarchicalFacets)) {
            // Display hierarchical facet levels nicely
            $separator = isset($hierarchicalFacetSeparators[$field])
                ? $hierarchicalFacetSeparators[$field]
                : '/';
            $filter['displayText'] = $facetHelper->formatDisplayText(
                $filter['displayText'], true, $separator
            );
            if ($translate) {
                $domain = $this->getOptions()->getTextDomainForTranslatedFacet(
                    $field
                );
                $filter['displayText'] = $this->translate(
                    [$domain, $filter['displayText']]
                );
            }
        }

        return $filter;
    }

    /**
     * Build a string for onscreen display showing the
     *   query used in the search (not the filters).
     *
     * @return string user friendly version of 'query'
     */
    public function getDisplayQuery()
    {

        $showField = [$this->getOptions(), 'getHumanReadableFieldName'];

        // Build display query:
        return self::display($this->getQuery(), $showField);
    }

    public static function display(AbstractQuery $query, $showName)
    {
        // Simple case -- basic query:
        if ($query instanceof  \VuFindSearch\Query\Query) {
            return $query->getString();
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
                            . $group->getString();
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

    /**
     * Überschreibt Methode aus VuFind\Search\Base\Params um den Sortierschlüssel part_of_PPN zuzulassen.
     *
     * Set the sorting value (note: sort will be set to default if an illegal
     * or empty value is passed in).
     *
     * @param string $sort  New sort value (null for default)
     * @param bool   $force Set sort value without validating it?
     *
     * @return void
     */
    public function setSort($sort, $force = false)
    {
        // Skip validation if requested:
        if ($force) {
            $this->sort = $sort;
            return;
        }

        // Validate and assign the sort value:
        $valid = array_keys($this->getOptions()->getSortOptions());
        if (!empty($sort) && (in_array($sort, $valid) || strpos( $sort, "part_of_") !== false)) {
            $this->sort = $sort;
        } else {
            $this->sort = $this->getDefaultSort();
        }

        // In RSS mode, we may want to adjust sort settings:
        if (!$this->skipRssSort && $this->getView() == 'rss') {
            $this->sort = $this->getOptions()->getRssSort($this->sort);
        }
    }
}