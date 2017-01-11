<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 10.01.17
 * Time: 11:30
 */

namespace Hebis\Search;


use VuFindSearch\Backend\Exception\BackendException;
use VuFindSearch\ParamBag;
use VuFindSearch\Query\AbstractQuery;
use VuFindSearch\Response\RecordCollectionInterface;

class Service extends \VuFindSearch\Service
{

    /**
     * Perform a search and return a wrapped response.
     *
     * @param string              $backend Search backend identifier
     * @param AbstractQuery       $query   Search query
     * @param integer             $offset  Search offset
     * @param integer             $limit   Search limit
     * @param ParamBag            $params  Search backend parameters
     *
     * @return RecordCollectionInterface
     */
    public function searchTerms($backend, AbstractQuery $query, $offset = 0,
                           $limit = 20, ParamBag $params = null
    ) {
        $params  = $params ?: new ParamBag();
        $context = __FUNCTION__;
        $args = compact('backend', 'query', 'offset', 'limit', 'params', 'context');

        $backend  = $this->resolve($backend, $args);
        $args['backend_instance'] = $backend;

        $params->set("terms.prefix", $query->getAllTerms());
        $params->set('terms', 'true');
        $params->set('terms.fl', "suggest_3");
        $params->set('terms.limit', $limit);
        $params->set('terms.sort', 'count');

        $this->triggerPre($backend, $args);
        try {
            $response = $backend->terms($params);
        } catch (BackendException $e) {
            $this->triggerError($e, $args);
            throw $e;
        }
        $this->triggerPost($response, $args);
        return $response;
    }
}