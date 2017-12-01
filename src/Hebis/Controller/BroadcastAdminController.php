<?php


namespace Hebis\Controller;

use Hebis\Db\Table\Broadcast;
use VuFind\I18n\Translator\TranslatorAwareTrait;
use VuFindAdmin\Controller\AbstractAdmin;
use VuFind\Date\Converter;
use Zend\Http\Response;


/**
 * Class controls Static Pages administration
 *
 * @package Hebis\Controller
 * @author Roshak Zarhoun <roshakz@gmail.com>
 */
class BroadcastAdminController extends AbstractAdmin
{
    use TranslatorAwareTrait;

    /**
     * @var Broadcast
     */
    protected $table;
    protected $DateTimeConverter;

    public function __construct(Broadcast $table, $translator)
    {
        parent::__construct();
        $this->table = $table;
        $this->setTranslator($translator);
        $this->DateTimeConverter = new Converter();
    }

    public function homeAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-home');
        $lang = $this->getTranslatorLocale();
        $rows = $this->table->getAllByParameter($lang, null);
        $expired = $this->table->getAllByParameter($lang, null, true);
        $view->rows = $rows;
        $view->expired = $expired;
        return $view;
    }

    public function addAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-add');
        $allLanguages = array_slice($this->getConfig()->toArray()['Languages'], 1);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $view->langs = $allLanguages;

            $view->bcid = 0;
            $view->language = [];
            $view->message = [];
            $view->type = "info";
            $view->startDate = "";
            $view->expireDate = "";
            $view->hide = 0;

            return $view;
        }

        $this->table->persist($request);

        return $this->redirect()->toRoute('broadcastadmin', ['action' => 'home']);
    }

    public function editAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-edit');
        $allLanguages = array_slice($this->getConfig()->toArray()['Languages'], 1);
        $view->langs = $allLanguages;
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $language = [];
            $message = [];
            $bcid = $this->params()->fromRoute('bcid');
            $rowSet = $this->table->getBroadcastsById($bcid);
            $params = $rowSet->toArray();

            for ($i = 0; $i < count($params); ++$i) { // iterate over languages
                $lang = $params[$i]['language'];
                $language[$lang] = $lang;
                $message[$lang] = $params[$i]['message'];
            }
            $view->language = $language;
            $view->message = $message;
            $view->type = $params[0]['type'];
            $view->startDate = date("d.m.Y",strtotime($params[0]['startDate']));
            $view->expireDate = date("d.m.Y",strtotime($params[0]['expireDate']));

            $view->bcid = $params[0]['bcid'];
            $view->hide = $params[0]['hide'];
            return $view;
        }
        $this->table->persist($request);
        return $this->redirect()->toRoute('broadcastadmin', ['action' => 'home']);
    }

    /**
     * deletes a broadcast set
     * @return Response
     */
    public function deleteAction()
    {
        try {
            $bcid = $this->params()->fromRoute('bcid');
            $rows = $this->table->getBroadcastsById($bcid);
            foreach ($rows as $row) {
                $row->delete();
            }
        } catch (\Exception $e) {
            return $this->output(0, 'failed.' . '\n' . $e->getMessage(), 400);
        }
        return $this->output(1, 'done', 200);
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
        if ($this->outputMode !== 'json') {
            throw new \Exception('Unsupported output mode: ' . $this->outputMode);
        } else {
            $headers->addHeaderLine('Content-type', 'application/javascript');
            $output = ['data' => $data, 'status' => $status];

            $response->setContent(json_encode($output));
            return $response;
        }
    }


}
