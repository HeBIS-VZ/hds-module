<?php
/**
 * "Search tabs" view helper
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 * Copyright (C) The National Library of Finland 2015-2016.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Hebis\View\Helper\Root;
use VuFind\Search\Base\Results,
    VuFind\Search\Results\PluginManager,
    VuFind\Search\SearchTabsHelper,
    Zend\View\Helper\Url,
    Zend\Http\Request;

/**
 * "Search tabs" view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class SearchTabs extends \VuFind\View\Helper\Root\SearchTabs
{
    /**
     * @var SearchMemory
     */
    protected $searchMemory;

    /**
     * Constructor
     *
     * @param PluginManager    $results Search results plugin manager
     * @param Url              $url     URL helper
     * @param SearchTabsHelper $helper  Search tabs helper
     * @param SearchMemory
     */
    public function __construct(PluginManager $results, Url $url,
        SearchTabsHelper $helper
    ) {
        parent::__construct($results, $url, $helper);
    }

    /**
     * Determine information about search tabs
     *
     * @param string $activeSearchClass The search class ID of the active search
     * @param string $query             The current search query
     * @param string $handler           The current search handler
     * @param string $type              The current search type (basic/advanced)
     * @param array  $hiddenFilters     The current hidden filters
     *
     * @return array
     */
    public function getTabConfig($activeSearchClass, $query, $handler,
        $type = 'basic', $hiddenFilters = []
    ) {
        $this->searchMemory = $this->getView()->searchMemory();

        $retVal = [];
        $matchFound = false;
        $allFilters = $this->helper->getTabFilterConfig();
        foreach ($this->helper->getTabConfig() as $key => $label) {
            $class = $this->helper->extractClassName($key);
            $filters = isset($allFilters[$key]) ? (array)$allFilters[$key] : [];
            if ($class == $activeSearchClass
                && $this->helper->filtersMatch($class, $hiddenFilters, $filters)
            ) {
                $matchFound = true;
                $retVal[] = $this->createSelectedTab($key, $class, $label);
            } else if ($type == 'basic') {
                if (!isset($activeOptions)) {
                    $activeOptions
                        = $this->results->get($activeSearchClass)->getOptions();
                }
                $newUrl = $this->remapBasicSearch(
                    $activeOptions, $class, $query, $handler, $filters
                );
                $retVal[] = $this->createBasicTab($key, $class, $label, $newUrl);
            } else if ($type == 'advanced') {
                $retVal[] = $this->createAdvancedTab_($key, $class, $label, $filters, $activeSearchClass, $query, $handler);
            } else if (($controller = $this->getView()->controllerName()) === "Record" || $controller === "Edsrecord") {
                list($query, $handler, $type) = $this->extractQueryAndHandlerAndType($this->searchMemory->getLastUrl($activeSearchClass));
                if (!isset($activeOptions)) {
                    $activeOptions
                        = $this->results->get($activeSearchClass)->getOptions();
                }
                if ($type === "basic") { //basic search
                    $newUrl = $this->remapBasicSearch(
                        $activeOptions, $class, $query, $handler, $filters
                    );
                    $retVal[] = $this->createBasicTab($key, $class, $label, $newUrl);
                } else { //advanced search
                    $retVal[] = $this->createAdvancedTab_($key, $class, $label, $filters, $activeSearchClass);
                }
            }
            else {
                $retVal[] = $this->createHomeTab($key, $class, $label, $filters);
            }
        }
        if (!$matchFound && !empty($retVal)) {
            // Make the first tab for the given search class selected
            foreach ($retVal as &$tab) {
                if ($tab['class'] == $activeSearchClass) {
                    $tab['selected'] = true;
                    break;
                }
            }
        }

        return $retVal;
    }

    private function extractQueryAndHandlerAndType($url)
    {
        $query = null;
        $handler = null;
        $type = null;
        $url = substr(urldecode($url), strpos(urldecode($url),"?") + 1);
        $url = preg_replace("/&amp;/", "&", $url);
        parse_str($url, $queryArray);
        if (isset($queryArray["lookfor"])) {
            $query = $queryArray["lookfor"];
            $handler = $queryArray["type"];
            $type = "basic";
        } else if (isset($lookfor0)) {
            $query = $queryArray["lookfor0"];
            $handler = $queryArray["type0"];
            $type = "advanced";
        }
        return [$query, $handler, $type];
    }

    /**
     * Create information representing an advanced search tab.
     *
     * @param string $id Tab ID
     * @param string $class Search class ID
     * @param string $label Display text for tab
     * @param array $filters Tab filters
     *
     * @param $activeClass
     * @param $query
     * @param $handler
     * @return array
     */
    protected function createAdvancedTab_($id, $class, $label, $filters, $activeClass, $query = "", $handler = "")
    {
        if (empty($this->searchMemory)) {
            $this->searchMemory = $this->getView()->searchMemory();
        }
        // If an advanced search is available, link there; otherwise, just go
        // to the search home:
        if ($class != $activeClass) {
            $activeOptions
                = $this->results->get($activeClass)->getOptions();
            $newUrl = $this->remapBasicSearch(
                $activeOptions, $class, $query, $handler, $filters
            );
            if ($class === "EDS") {
                $newUrl = str_replace("Search", "Advanced", $newUrl);
            } else {
                $newUrl = str_replace("Results", "Advanced", $newUrl);
            }


        } else {
            $results = $this->results->get($class);
            $options = $results->getOptions();
            $advSearch = $options->getAdvancedSearchAction();
            $newUrl = $this->searchMemory->getLastUrl($class);
            $newUrl = str_replace("Results", "Advanced", $newUrl);
        }

        return [
            'id' => $id,
            'class' => $class,
            'label' => $label,
            'selected' => false,
            'url' => $newUrl
        ];
    }
}
