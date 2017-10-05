<?php

namespace Hebis\Search\Solr;


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
}