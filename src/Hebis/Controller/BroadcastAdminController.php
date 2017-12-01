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
    protected $outputMode;

    // define http status constants
    const STATUS_OK = 'OK';
    const STATUS_ERROR = 'ERROR';
    const STATUS_NEED_AUTH = 'NEED_AUTH';

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
        $view->langs = $allLanguages;
        $request = $this->getRequest();

        $view->language = [];
        $view->message = [];
        $view->type = "info";
        $view->expireDate = "";

        if (!$request->isPost()) {
            return $view;
        }

        $Converter = new Converter();

        $bcid = $this->table->getLastBcId() + 1;
        //$bcid++;

        $language = $this->params()->fromPost('bc-lang');
        $message = $this->params()->fromPost('bc-message');
        $type = $this->params()->fromPost('bc-type');
        $expireDate = $this->DateTimeConverter->convertFromDisplayDate('Y-m-d', $this->params()->fromPost('bc-expireDate'));

        $notEmpty = false;

        for ($i = 0; $i < count($allLanguages); ++$i) {
            $len = strlen(strip_tags($message[$i]));
            $notEmpty |= ($len > 0);
        }

        if (!$notEmpty) {
            $view->error = true;
            return $view;
        }
        $show = ($this->params()->fromPost('bc-show') === "on") ? 1 : 0;
        for ($i = 0; $notEmpty && $i < sizeof($allLanguages); $i++) {
            $this->saveRow(
                $bcid,
                $language[$i],
                $message[$i],
                $type,
                $expireDate,
                $show
            );
        }
        return $this->redirect()->toRoute('broadcastadmin', ['action' => 'home']);
    }

    public function editAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-edit');
        $allLanguages = array_slice($this->getConfig()->toArray()['Languages'], 1);
        $view->langs = $allLanguages;

        $bcid = $this->params()->fromRoute('bcid');
        $request = $this->getRequest();
        $rowSet = $this->table->getBroadcastSetById($bcid);
        $view->rowSet = $rowSet->getDataSource();
        $view->expireDate = $this->DateTimeConverter->convertToDisplayDate('Y-m-d', $rowSet->current()->expireDate);

        if (!$request->isPost()) {
            return $view;
        }

        $i = 0;
        foreach ($rowSet as $row) {
            $row->message = $this->params()->fromPost('bc-message')[$i];
            $row->type = $this->params()->fromPost('bc-type');
            $row->expireDate = $this->DateTimeConverter->convertFromDisplayDate('Y-m-d', $this->params()->fromPost('bc-expireDate'));
            $row->save();
            ++$i;
        }

        return $this->redirect()->toRoute('broadcastadmin', ['action' => 'home']);
    }

    /**
     * deletes a broadcast set
     * @return Response
     */
    public function deleteAjaxAction()
    {
        $this->outputMode = 'json';
        try {
            $bcid = $this->params()->fromRoute('bcid');
            $rows = $this->table->getBroadcastSetById($bcid);
            foreach ($rows as $row) {
                $row->delete();
            }
        } catch (\Exception $e) {
            return $this->output(0, self::STATUS_ERROR . '\n' . $e->getMessage(), 400);
        }
        return $this->output(1, self::STATUS_OK, 200);
    }

    /* saves a single row to the table */
    private function saveRow($bcid, $language, $message, $type, $expireDate, $show)
    {
        $row = $this->table->createRow();
        $row->bcid = $bcid;
        $row->language = $language;
        $row->message = $message;
        $row->type = $type;
        $row->expireDate = $expireDate;
        $row->show = $show;
        $row->save();
    }


    public function visibilityAjaxAction()
    {
        $this->outputMode = 'json';
        try {
            $bcid = $this->params()->fromRoute('bcid');
            $broadcasts = $this->table->getBroadcastSetById($bcid);
            foreach ($broadcasts as $broadcast) {
                $broadcast->show == 1 ? $broadcast->show = 0 : $broadcast->show = 1;
                $broadcast->save();
            }
        } catch (\Exception $e) {
            $this->output($e->getMessage() . '\n' . 'Change Visibility Failed!', self::STATUS_ERROR, 400);
        }

        //$this->layout()->setTemplate('pageadmin/list');
        return $this->output($broadcast->show == 1, self::STATUS_OK, 200);
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

        $headers->addHeaderLine('Content-type', 'application/javascript');
        $output = ['data' => $data, 'status' => $status];

        $response->setContent(json_encode($output));
        return $response;
    }


}
