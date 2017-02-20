<?php
/**
 * Ajax Controller Module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  Controller
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:controllers Wiki
 */
namespace Hebis\Controller;
use VuFind\Exception\Auth as AuthException;

/**
 * This controller handles global AJAX functionality
 *
 * @category VuFind
 * @package  Controller
 * @author   Chris Hallberg <challber@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:controllers Wiki
 */
class AjaxController extends \VuFind\Controller\AjaxController
{


    /**
     * AJAX for timeline feature (PubDateVisAjax)
     *
     * @param array $fields Solr fields to retrieve data from
     *
     * @author Chris Hallberg <crhallberg@gmail.com>
     * @author Till Kinstler <kinstler@gbv.de>
     *
     * @return \Zend\Http\Response
     */
    protected function getVisDataAjax($fields = ['publish_date'])
    {
        $this->disableSessionWrites();  // avoid session write timing bug
        $results = $this->getResultsManager()->get('Solr');
        $params = $results->getParams();
        $params->initFromRequest($this->getRequest()->getQuery());
        foreach ($this->params()->fromQuery('hf', []) as $hf) {
            $params->addHiddenFilter($hf);
        }
        $params->getOptions()->disableHighlighting();
        $params->getOptions()->spellcheckEnabled(false);
        $filters = $params->getFilters();
        $dateFacets = $this->params()->fromQuery('facetFields');
        $dateFacets = empty($dateFacets) ? [] : explode(':', $dateFacets);
        $fields = $this->processDateFacets($filters, $dateFacets, $results);
        $facets = $this->processFacetValues($fields, $results);
        foreach ($fields as $field => $val) {
            $facets[$field]['min'] = $val[0] > 0 ? $val[0] : 0;
            $facets[$field]['max'] = $val[1] > 0 ? $val[1] : 0;
            $facets[$field]['removalURL']
                = $results->getUrlQuery()->removeFacet(
                    $field,
                    isset($filters[$field][0]) ? $filters[$field][0] : null,
                    false
                );
        }
        return $this->output($facets, self::STATUS_OK);
    }

    /**
     * Support method for getVisData() -- extract details from applied filters.
     *
     * @param array                       $filters    Current filter list
     * @param array                       $dateFacets Objects containing the date
     * ranges
     * @param \VuFind\Search\Solr\Results $results    Search results object
     *
     * @return array
     */
    protected function processDateFacets($filters, $dateFacets, $results)
    {
        $result = [];
        foreach ($dateFacets as $current) {
            $from = $to = '';
            if (isset($filters[$current])) {
                foreach ($filters[$current] as $filter) {
                    if (preg_match('/\[[\d\*]+ TO [\d\*]+\]/', $filter)) {
                        $range = explode(' TO ', trim($filter, '[]'));
                        $from = $range[0] == '*' ? '' : $range[0];
                        $to = $range[1] == '*' ? '' : $range[1];
                        break;
                    }
                }
            }
            $result[$current] = [$from, $to];
            $result[$current]['label']
                = $results->getParams()->getFacetLabel($current);
        }
        return $result;
    }

    /**
     * Support method for getVisData() -- filter bad values from facet lists.
     *
     * @param array                       $fields  Processed date information from
     * processDateFacets
     * @param \VuFind\Search\Solr\Results $results Search results object
     *
     * @return array
     */
    protected function processFacetValues($fields, $results)
    {
        $facets = $results->getFullFieldFacets(array_keys($fields));
        $retVal = [];
        foreach ($facets as $field => $values) {
            $newValues = ['data' => []];
            foreach ($values['data']['list'] as $current) {
                // Only retain numeric values!
                if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}[T][0-9]{2}:[0-9]{2}:[0-9]{2}[A-Z]$/", $current['value'])) {
                    $newValues['data'][]
                        = [substr($current['value'], 0, 4), $current['count']];
                }
            }
            $retVal[$field] = $newValues;
        }
        return $retVal;
    }

}
