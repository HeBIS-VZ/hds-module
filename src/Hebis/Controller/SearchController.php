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

class SearchController extends \VuFind\Controller\SearchController
{

    const STATUS_OK = 'OK';                  // good
    const STATUS_ERROR = 'ERROR';            // bad
    const STATUS_NEED_AUTH = 'NEED_AUTH';    // must login first


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
        $backlink = $this->params()->fromQuery("backlink");

        if (preg_match("/\s([&+])\s/u", $lookfor)) {
            $encodedLookfor = $this->solrSpecialChars($lookfor);
            $this->getRequest()->getQuery()->set("lookfor", $encodedLookfor); //call by reference
            $view = parent::resultsAction();
            $view->params->getQuery()->setString($lookfor);
        } else {
            $view = parent::resultsAction();
        }


        /** @var Results $results */
        $results = $view->results;
        if ($results->getResultTotal() === 1) {
            /** @var SolrMarc $record */
            $record = $results->getResults()[0];
            $ppn = $record->getPPN();
            $params = ['id' => $ppn];
            if (!empty($backlink)) {
                $params = array_merge($params, ['backlink' => $backlink]);
            }

            return $this->forwardTo("record", "home", $params);
        } else {
            if ($results->getResultTotal() === 0) {
                $request = $this->getRequest()->getQuery()->toArray()
                    + $this->getRequest()->getPost()->toArray()
                    + ["searchId" => $results->getSearchId()];
                return $this->forwardTo("search", "record_not_found", $request);
            }
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
                'hierarchicalFacetSortOptions' => $this->getHierarchicalFacetSortSettings()
            ]
        );

        $view->searchId = $this->params()->fromRoute('searchId',false);

        $view->backlink = $this->params()->fromQuery("backlink");
        $this->params()->fromQuery("searchId");
        $view->params = $params = $this->getRequest()->getQuery()->toArray()
            + $this->getRequest()->getPost()->toArray();

        $lookfor0 = $this->params()->fromQuery("lookfor0");

        $view->searchType = !empty($lookfor0) && is_array($lookfor0) ? 'advanced' : 'simple';

        $view->lookfor = !empty($lookfor0) && is_array($lookfor0) ? $this->params()->fromQuery("lookfor0") : $this->params()->fromQuery("lookfor");
        return $view;
    }


    /**
     * Overrides VuFind\Controller\AbstractSearch::rememberSearch in order to distinguish
     * Solr Searches from EDS Searches in SearchMemory
     * @param \VuFind\Search\Base\Results $results
     */
    public function rememberSearch($results)
    {
        // Only save search URL if the property tells us to...
        if ($this->rememberSearch) {
            $searchUrl = $this->url()->fromRoute(
                    $results->getOptions()->getSearchAction()
                ) . $results->getUrlQuery()->getParams(false);
            $this->getSearchMemory()->rememberLastSearchOf('Solr', $searchUrl);
        }

        // Always save search parameters, since these are namespaced by search
        // class ID.
        $this->getSearchMemory()->rememberParams($results->getParams());
    }

    /**
     * @return \Zend\Http\Response
     */
    public function ajaxAction()
    {
        $this->outputMode = "json";
        $view = $this->resultsAction();
        $results = $view->results;
        $resultTotal = $results->getResultTotal();
        return $this->output($resultTotal, static::STATUS_OK);
    }

    /**
     * Send output data and exit.
     *
     * @param mixed $data The response data
     * @param string $status Status of the request
     * @param int $httpCode A custom HTTP Status Code
     *
     * @return \Zend\Stdlib\ResponseInterface
     * @throws \Exception
     */
    protected function output($data, $status, $httpCode = null)
    {
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Cache-Control', 'no-cache, must-revalidate');
        $headers->addHeaderLine('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        if ($httpCode !== null) {
            $response->setStatusCode($httpCode);
        }

        $headers->addHeaderLine('Content-type', 'application/javascript');
        $output = ['data' => $data, 'status' => $status];
        //if ('development' == APPLICATION_ENV && count(self::$php_errors) > 0) {
        //    $output['php_errors'] = self::$php_errors;
        //}
        $response->setContent(json_encode($output));
        return $response;

    }

    private function solrSpecialChars($lookfor)
    {

        return preg_replace_callback("/\s([&+])\s/", function($matches) {
            return " ".self::SPECIAL_CHARS_MAP[$matches[1]]." ";
        }, $lookfor);
    }
}
