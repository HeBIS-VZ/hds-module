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

use Zend\Stdlib\Parameters;

/**
 * Class EdsController
 * @package Hebis\Controller
 * @author Sebastian Böttger <boettger@hebis-uni-frankfurt.de>
 */
class EdsController extends \VuFind\Controller\EdsController
{


    const STATUS_OK = 'OK';                  // good
    const STATUS_ERROR = 'ERROR';            // bad
    const STATUS_NEED_AUTH = 'NEED_AUTH';    // must login first

    /**
     * @var String
     */
    protected $outputMode;

    /**
     * Array of PHP errors captured during execution
     *
     * @var array
     */
    protected static $php_errors = [];

    /**
     * @return \Zend\Http\Response
     */
    public function ajaxAction()
    {
        $this->outputMode = "json";
        $view = $this->resultsAction();
        $results = $view->results;
        $resultTotal = $results->getResultTotal();
        return $this->output($resultTotal, self::STATUS_OK);
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
        if ($this->outputMode == 'json') {
            $headers->addHeaderLine('Content-type', 'application/javascript');
            $output = ['data' => $data, 'status' => $status];
            if ('development' == APPLICATION_ENV && count(self::$php_errors) > 0) {
                $output['php_errors'] = self::$php_errors;
            }
            $response->setContent(json_encode($output));
            return $response;
        } else {
            if ($this->outputMode == 'plaintext') {
                $headers->addHeaderLine('Content-type', 'text/plain');
                $response->setContent($data ? $status . " $data" : $status);
                return $response;
            } else {
                throw new \Exception('Unsupported output mode: ' . $this->outputMode);
            }
        }
    }

    /**
     * Clean ISSN before starting search request
     * @return \Zend\View\Model\ViewModel
     */
    public function resultsAction()
    {
        $this->cleanISXNParameter();
        return parent::resultsAction();
    }

    /**
     * removes '-' from the search query
     */
    public function cleanISXNParameter()
    {
        $issnPattern = "/(\d{4})-(\d{4})/";
        if (!empty($type = $this->getRequest()->getQuery()->get("type"))) {
            $lookfor = $this->getRequest()->getQuery()->get("lookfor");
            if ($type === "IS") {
                if (preg_match($issnPattern, $lookfor, $match)) {
                    $lookfor = $match[1] . $match[2];
                    $this->getRequest()->getQuery()->set("lookfor", $lookfor);
                }
            }
            if ($type === "IB") {
                $lookfor = str_replace("-", "", $lookfor);
                $this->getRequest()->getQuery()->set("lookfor", $lookfor);
            }
        } elseif (!empty($type0 = $this->getRequest()->getQuery()->get("type0"))) {
            if (is_array($type0)) {
                if (($pos = array_search("IS", $type0)) !== false) {
                    $lookfor0 = $this->getRequest()->getQuery()->get("lookfor0");
                    if (preg_match($issnPattern, $lookfor0[$pos], $match)) {
                        $lookfor[$pos] = $match[1] . $match[2];
                        $this->getRequest()->getQuery()->set("lookfor0", $lookfor);
                    }
                } elseif (($pos = array_search("IB", $type0)) !== false) {
                    $lookfor0 = $this->getRequest()->getQuery()->get("lookfor0");
                    $lookfor_ = str_replace("-", "", $lookfor0[$pos]);
                    $lookfor[$pos] = $lookfor_;
                    $this->getRequest()->getQuery()->set("lookfor0", $lookfor);
                }
            }
        }
    }

    public function rememberSearch($results)
    {
        // Only save search URL if the property tells us to...
        if ($this->rememberSearch) {
            $searchUrl = $this->url()->fromRoute(
                    $results->getOptions()->getSearchAction()
                ) . $results->getUrlQuery()->getParams(false);
            $this->getSearchMemory()->rememberLastSearchOf('EDS', $searchUrl);
        }

        // Always save search parameters, since these are namespaced by search
        // class ID.
        $this->getSearchMemory()->rememberParams($results->getParams());
    }
}
