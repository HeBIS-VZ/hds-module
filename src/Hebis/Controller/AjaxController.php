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

use VuFind\Controller\AbstractSearch;
use VuFind\Controller\EdsController;
use VuFind\Exception\Auth as AuthException;

/**
 * Class AjaxController
 * @package Hebis\Controller
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class AjaxController extends \VuFind\Controller\AjaxController
{

    const MERGE_BOUNDARY = 100;

    /**
     * AJAX for timeline feature (PubDateVisAjax)
     *
     * @param array $fields Solr fields to retrieve data from
     *
     * @author Chris Hallberg <crhallberg@gmail.com>
     * @author Till Kinstler <kinstler@gbv.de>
     * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
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
     * @param array $filters Current filter list
     * @param array $dateFacets Objects containing the date
     * ranges
     * @param \VuFind\Search\Solr\Results $results Search results object
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
     * @param array $fields Processed date information from
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
            $newValues = [
                'data' => [],
                'minYear' => intval(date("Y", time())),
                'maxYear' => 0,
                'minCount' => 0,
                'maxCount' => 0
            ];
            list($to, $from, $hasDateFilter, $newValues) = $this->dateFilters($results, $newValues);
            foreach ($values['data']['list'] as $current) {
                // Only retain numeric values!
                if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}[T][0-9]{2}:[0-9]{2}:[0-9]{2}[A-Z]$/",
                    $current['value'])) {
                    $currentYear = intval(substr($current['value'], 0, 4));
                    if ($currentYear < 1400) {
                        continue;
                    }
                    $currentCount = $current['count'];
                    $val = [$currentYear, $currentCount];
                    if ($hasDateFilter) {
                        if (strcmp($current['value'], $from) >= 0 && strcmp($current['value'], $to) <= 0) {
                            $val[] = true;
                        } else {
                            $val[] = false;
                        }
                    } else {
                        $val[] = false;
                    }

                    $newValues['data'][$currentYear] = $val;
                    $newValues = $this->maxMinValues($newValues, $val);
                }
            }

            for ($i = $newValues['minYear']; $i <= $newValues['maxYear']; ++$i) {
                if (!array_key_exists($i, $newValues['data'])) {
                    $val = [$i, 0];
                    $val[] = $i >= $newValues['minYear'] && $i <= $newValues['maxYear'] ? true : false;
                    $newValues['data'][$i] = $val;
                }
            }

            usort($newValues['data'], function ($a, $b) {
                return strcmp($a[0], $b[0]);
            });


            if (count($newValues['data']) > self::MERGE_BOUNDARY) {

                $newValues = $this->mergeYears($newValues);
            }
            $retVal[$field] = $newValues;
            $retVal[$field]['data'] = array_values($newValues['data']);
        }
        return $retVal;
    }

    /**
     * @param $newValues
     * @return array
     */
    protected function mergeYears($newValues)
    {
        $numOfSummarizedFields = floor(count($newValues['data']) / self::MERGE_BOUNDARY) + 1;
        $i = 1;
        $list = [];
        $val = [0, 0, false];
        foreach ($newValues['data'] as $key => $value) {
            if ($i <= $numOfSummarizedFields) {
                if ($i === 1) {
                    $val[0] = $value[0];
                }
                $val[1] += $value[1];
                $val[2] = $val[2] || $value[2];
                if ($i < $numOfSummarizedFields) {
                    ++$i;
                    continue;
                }
            }
            $list[$val[0]] = $val;
            $newValues = $this->maxMinValues($newValues, $val);
            $val = [0, 0, false];
            $i = 1;
        }
        $newValues['data'] = $list;
        return $newValues;
    }

    /**
     * @param $newValues
     * @param $val
     * @return mixed
     */
    protected function maxMinValues($newValues, $val)
    {
        if ($val[0] < $newValues['minYear']) {
            $newValues['minYear'] = $val[0];
        }
        if ($val[0] > $newValues['maxYear']) {
            $newValues['maxYear'] = $val[0];
        }
        if ($val[1] < $newValues['minCount']) {
            $newValues['minCount'] = $val[1];
        }
        if ($val[1] > $newValues['maxCount']) {
            $newValues['maxCount'] = $val[1];
        }
        return $newValues;
    }

    /**
     * @param @param \VuFind\Search\Solr\Results $results Search results object
     * @param $newValues
     * @return array
     */
    protected function dateFilters($results, $newValues)
    {
        $filters = $results->getParams()->getFilterList(true);
        $from = $to = '';
        $hasDateFilter = false;
        foreach ($filters as $filter) {
            foreach ($filter as $currentFilter) {
                $match = [];
                if (preg_match("/^\[([0-9]{4}\-[0-9]{2}\-[0-9]{2}[T][0-9]{2}:[0-9]{2}:[0-9]{2}[A-Z]) TO ([0-9]{4}\-[0-9]{2}\-[0-9]{2}[T][0-9]{2}:[0-9]{2}:[0-9]{2}[A-Z])\]$/",
                    $currentFilter["value"], $match)) {
                    $from = $match[1];
                    $to = $match[2];
                    $hasDateFilter = true;
                }
            }
        }
        $newValues['rangeFrom'] = intval(substr($from, 0, 4));
        $newValues['rangeTo'] = intval(substr($to, 0, 4));
        return array($to, $from, $hasDateFilter, $newValues);
    }

}
