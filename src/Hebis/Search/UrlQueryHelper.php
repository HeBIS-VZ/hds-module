<?php
/**
 * Class to help build URLs and forms in the view based on search settings.
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
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace Hebis\Search;
use VuFindSearch\Query\QueryGroup;

/**
 * Class to help build URLs and forms in the view based on search settings.
 *
 * @category VuFind
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class UrlQueryHelper extends \VuFind\Search\UrlQueryHelper
{


    /**
     * Get an array of URL parameters.
     * @return array
     * @throws \Exception
     */
    public function getParamArray()
    {
        $params = $this->defaultParams;

        // Build all the URL parameters based on search object settings:
        if (!$this->suppressQuery) {
            if ($this->params->getSearchType() == 'advanced') {
                $query = $this->params->getQuery();
                if ($query instanceof QueryGroup) {
                    $params['join'] = $query->getOperator();
                    foreach ($query->getQueries() as $i => $current) {
                        if ($current instanceof QueryGroup) {
                            $operator = $current->isNegated()
                                ? 'NOT' : $current->getOperator();
                            $params['bool' . $i] = [$operator];
                            foreach ($current->getQueries() as $inner) {
                                if (!isset($params['lookfor' . $i])) {
                                    $params['lookfor' . $i] = [];
                                }
                                if (!isset($params['type' . $i])) {
                                    $params['type' . $i] = [];
                                }
                                $params['lookfor' . $i][] = $inner->getString();
                                $params['type' . $i][] = $inner->getHandler();
                                if (null !== ($op = $inner->getOperator())) {
                                    $params['op' . $i][] = $op;
                                }
                            }
                        } else {
                            throw new \Exception('Unexpected Query object.');
                        }
                    }
                } else {
                    throw new \Exception('Unexpected Query object.');
                }
            } else {
                $search = $this->params->getDisplayQueryInputField();
                if (!empty($search)) {
                    $params[$this->basicSearchParam] = $search;
                }
                $type = $this->params->getSearchHandler();
                if (!empty($type)) {
                    $params['type'] = $type;
                }
            }
        }
        $sort = $this->params->getSort();
        if (!is_null($sort)
            && $sort != $this->params->getDefaultSort()
        ) {
            $params['sort'] = $sort;
        }
        $limit = $this->params->getLimit();
        if (!is_null($limit)
            && $limit != $this->options->getDefaultLimit()
        ) {
            $params['limit'] = $limit;
        }
        $view = $this->params->getView();
        if (!is_null($view)
            && $view != $this->options->getDefaultView()
        ) {
            $params['view'] = $view;
        }
        if ($this->params->getPage() != 1) {
            $params['page'] = $this->params->getPage();
        }
        $filters = $this->params->getFilters();
        if (!empty($filters)) {
            $params['filter'] = [];
            foreach ($filters as $field => $values) {
                foreach ($values as $current) {
                    $params['filter'][] = $field . ':"' . $current . '"';
                }
            }
        }
        $hiddenFilters = $this->params->getHiddenFilters();
        if (!empty($hiddenFilters)) {
            foreach ($hiddenFilters as $field => $values) {
                foreach ($values as $current) {
                    $params['hiddenFilters'][] = $field . ':"' . $current . '"';
                }
            }
        }
        $shards = $this->params->getSelectedShards();
        if (!empty($shards)) {
            sort($shards);
            $defaultShards = $this->options->getDefaultSelectedShards();
            sort($defaultShards);
            if (implode(':::', $shards) != implode(':::', $defaultShards)) {
                $params['shard'] = $shards;
            }
        }
        if ($this->params->hasDefaultsApplied()) {
            $params['dfApplied'] = 1;
        }

        return $params;
    }
}
