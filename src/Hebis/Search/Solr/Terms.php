<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 10.01.17
 * Time: 11:25
 */

namespace Hebis\Search\Solr;


class Terms extends \VuFind\Search\Solr\Results
{

    /**
     * Return search service.
     *
     * @return SearchService
     *
     * @todo May better error handling, throw a custom exception if search service
     * not present
     */
    protected function getSearchService()
    {
        if (!$this->searchService) {
            $this->searchService = $this->getServiceLocator()->get('VuFind\Terms');
        }
        return $this->searchService;
    }

    /**
     * Returns the stored list of facets for the last search
     *
     * @param array $filter Array of field => on-screen description listing
     * all of the desired facet fields; set to null to get all configured values.
     *
     * @return array        Facets data arrays
     */
    public function getFacetList($filter = null)
    {
        return [];
    }

}