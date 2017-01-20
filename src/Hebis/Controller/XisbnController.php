<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 19.01.17
 * Time: 10:44
 */

namespace Hebis\Controller;

use Hebis\Connection\WorldCatUtils as WorldCatUtilsService;
use VuFind\Controller\AbstractBase;
use VuFind\Controller\SearchController;

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
        $isbns = $this->worldCatUtils->getXISBN($isbn);

        $lookfor = "isxn:(".implode(" ", $isbns).")";
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
        $view->results = $results;
        // Make view

        return $view;
    }


    /**
     * Send output data and exit.
     *
     * @param mixed  $data     The response data
     * @param string $status   Status of the request
     * @param int    $httpCode A custom HTTP Status Code
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

}