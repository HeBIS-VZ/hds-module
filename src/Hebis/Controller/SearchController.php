<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 25.01.17
 * Time: 16:35
 */

namespace Hebis\Controller;


class SearchController extends \VuFind\Controller\SearchController
{

    public function homeAction()
    {
        $results = $hierarchicalFacets = $hierarchicalFacetSortOptions = [];
        try {
            $results = $this->getHomePageFacets();
            $hierarchicalFacets = $this->getHierarchicalFacets();
            $hierarchicalFacetSortOptions = $this->getHierarchicalFacetSortSettings();
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
        return $this->createViewModel(
            [
                'results' => $results,
                'hierarchicalFacets' => $hierarchicalFacets,
                'hierarchicalFacetSortOptions'
                => $hierarchicalFacetSortOptions
            ]
        );
    }
}