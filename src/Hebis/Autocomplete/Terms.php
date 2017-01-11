<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 10.01.17
 * Time: 10:55
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