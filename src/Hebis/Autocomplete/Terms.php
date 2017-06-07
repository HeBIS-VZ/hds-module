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

namespace Hebis\Autocomplete;


use Hebis\Search\Service as SearchService;
use VuFind\Autocomplete\AutocompleteInterface;
use VuFindSearch\ParamBag;
use VuFindSearch\Query\Query;

class Terms implements AutocompleteInterface
{

    protected $searchService;

    protected $filters;

    protected $searchConfig;

    /**
     * Constructor
     *
     * @param \VuFind\Search\Results\PluginManager $results Results plugin manager
     */
    public function __construct(\VuFind\Search\Results\PluginManager $results, $searchConfig)
    {
        $this->searchService = new SearchService();
        $this->searchConfig = $searchConfig;
    }

    /**
     * This method returns an array of strings matching the user's query for
     * display in the autocomplete box.
     *
     * @param string $query The user query
     *
     * @return array        The suggestions for the provided query
     */
    public function getSuggestions($query)
    {
        //$query = $this->mungeQuery($query);
        $query = new Query(mb_strtolower($query), 'Solr');

        $key = $this->searchConfig->Autocomplete->suggest_field;

        $params = new ParamBag();
        $params->set("terms.fl", $key);

        $results = $this->searchService
            ->searchTerms('Solr', $query, 0, 20, $params)
            ->getFieldTerms($key);

        $arr = [];
        foreach ($results as $term => $value) {
            $arr[] = $term;
        }

        return $arr;
    }

    /**
     * Set parameters that affect the behavior of the autocomplete handler.
     * These values normally come from the search configuration file.
     *
     * @param string $params Parameters to set
     *
     * @return void
     */
    public function setConfig($params)
    {

    }

    /**
     * Process the user query to make it suitable for a Solr query.
     *
     * @param string $query Incoming user query
     *
     * @return string       Processed query
     */
    protected function mungeQuery($query)
    {
        // Modify the query so it makes a nice, truncated autocomplete query:
        $forbidden = [':', '(', ')', '*', '+', '"'];
        $query = str_replace($forbidden, " ", $query);
        /*
        if (substr($query, -1) != " ") {
            $query .= "*";
        }
        */
        return $query;
    }

}
