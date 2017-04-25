<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an
 * extension of the open source library search engine VuFind, that
 * allows users to search and browse beyond resources. More
 * Information about VuFind you will find on http://www.vufind.org
 *
 * Copyright (C) 2017
 * HeBIS Verbundzentrale des HeBIS-Verbundes
 * Goethe-Universität Frankfurt / Goethe University of Frankfurt
 * http://www.hebis.de
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Hebis\Controller;

use Hebis\RecordDriver\SolrMarc;
use Hebis\Search\Solr\Results;

/**
 * Class SearchController
 * @package Hebis\Controller
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SearchController extends \VuFind\Controller\SearchController
{

    const SPECIAL_CHARS_MAP = [
        "+" => "und",
        "&" => "und"
    ];

    public function homeAction()
    {
        $results = $hierarchicalFacets = $hierarchicalFacetSortOptions = [];
        try {
            $results = $this->getHomePageFacets();
            $hierarchicalFacets = $this->getHierarchicalFacets();
            $hierarchicalFacetSortOptions = $this->getHierarchicalFacetSortSettings();
        } catch (\Exception $e) {
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

    public function resultsAction()
    {
        //results->getUrlQuery()
        $lookfor = $this->params()->fromQuery("lookfor");

        if (preg_match("/\s([&+])\s/u", $lookfor)) {
            $encodedLookfor = $this->solrSpecialChars($lookfor);
            $this->getRequest()->getQuery()->set("lookfor", $encodedLookfor); //call by reference
        }
        $view = parent::resultsAction();
        $view->params->getQuery()->setString($lookfor);

        /** @var Results $results */
        $results = $view->results;
        if ($results->getResultTotal() === 1) {
            /** @var SolrMarc $record */
            $record = $results->getResults()[0];
            $ppn = $record->getPPN();
            return $this->forwardTo("record_finder", "home", ['id' => $ppn]);

        } else if ($results->getResultTotal() === 0) {
            $lookfor = $this->params()->fromQuery("lookfor");
            return $this->forwardTo("search", "record_not_found", ["lookfor" => $lookfor]);
        }
        return $view; //else return results list
    }

    /**
     * @return \VuFind\Controller\ViewModel
     */
    public function recordNotFoundAction()
    {
        $view = $this->createViewModel(
            [
                'results' => $this->getHomePageFacets(),
                'hierarchicalFacets' => $this->getHierarchicalFacets(),
                'hierarchicalFacetSortOptions'
                => $this->getHierarchicalFacetSortSettings()
            ]
        );
        $view->params = $this->params();
        $view->lookfor = $this->params()->fromQuery("lookfor");
        return $view;
    }

    private function solrSpecialChars($lookfor)
    {

        return preg_replace_callback("/\s([&+])\s/", function($matches) {
            return " ".self::SPECIAL_CHARS_MAP[$matches[1]]." ";
        }, $lookfor);
    }
}