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

use Hebis\Connection\WorldCatUtils as WorldCatUtilsService;
use Hebis\RecordDriver\SolrMarc;
use VuFind\Controller\AbstractBase;
use VuFind\Controller\SearchController;
use VuFind\Search\Base\Results;

class XisbnController extends SearchController
{

    /**
     * @var WorldCatUtilsService
     */
    protected $worldCatUtils;


    public function init()
    {
        $this->worldCatUtils = $this->serviceLocator->get('VuFind\WorldCatUtils');
    }

    /**
     * Handles passing data to the class
     *
     * @return mixed
     *
    public function xisbnAction()
    {
        // Set the output mode to JSON:
        $this->outputMode = 'html';

        // Call the method specified by the 'method' parameter; append Ajax to
        // the end to avoid access to arbitrary inappropriate methods.
        $callback = [$this, $this->params()->fromQuery('method') . 'Ajax'];
        if (is_callable($callback)) {
            try {
                return call_user_func($callback);
            } catch (\Exception $e) {
                $debugMsg = ('development' == APPLICATION_ENV)
                    ? ': ' . $e->getMessage() : '';
                return $this->output(
                    $this->translate('An error has occurred') . $debugMsg,
                    self::STATUS_ERROR,
                    500
                );
            }
        } else {
            return $this->output(
                $this->translate('Invalid Method'), self::STATUS_ERROR, 400
            );
        }
    }
    */

    public function xidAction()
    {
        $this->layout()->setTemplate('layout/lightbox');
        $view = $this->createViewModel();

        $isbn = $this->params()->fromQuery('isbn');
        $lookfor = "isxn:(" . implode(" ", $this->worldCatUtils->getXISBN($isbn)) . ")";
        $limit = 5;

        $runner = $this->getServiceLocator()->get('VuFind\SearchRunner');

        /**
         * @param \VuFind\Search\SearchRunner $runner
         * @param \VuFind\Search\Base\Params $params
         * @param string $searchId
         */
        $cb = function ($runner, $params, $searchId) use ($lookfor, $limit) {
            //$params->initFromRecordDriver($driver);
            $params->setLimit($limit);
            $params->setBasicSearch($lookfor);
        };

        $results = $runner->run([], $this->searchClassId, $cb);
        $results->filterResults(self::getFilterCallback([$isbn]));
        $view->results = $results;
        // Make view

        return $view;
    }


    /**
     * Send output data and exit.
     *
     * @param mixed $data The response data
     * @param string $status Status of the request
     * @param int $httpCode A custom HTTP Status Code
     *
     * @return \Zend\Http\Response
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

        try {
            parent::output($data, $status, $httpCode);
        } catch (\Exception $e) {
            if ($this->outputMode === 'html') {
                $headers->addHeaderLine('Content-type', 'application/javascript');
                $output = ['data' => $data, 'status' => $status];
                if ('development' == APPLICATION_ENV && count(self::$php_errors) > 0) {
                    $output['php_errors'] = self::$php_errors;
                }
                $response->setContent(json_encode($output));
                return $response;
            }
            throw $e;

        }
    }


    protected static function getFilterCallback(array $isbns)
    {

        return function ($record) use ($isbns) {
            /** @var SolrMarc $record */
            foreach ($record->getISBNs() as $isbn) {
                if (in_array($isbn, $isbns)) {
                    return false;
                }
            }
            return true;
        };
    }

}