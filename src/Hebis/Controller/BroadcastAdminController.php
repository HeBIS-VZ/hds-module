<?php


namespace Hebis\Controller;

use Hebis\Db\Table\Broadcast;
use VuFind\I18n\Translator\TranslatorAwareTrait;
use VuFindAdmin\Controller\AbstractAdmin;


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

    public function __construct(Broadcast $table, $translator)
    {
        parent::__construct();
        $this->table = $table;
        $this->setTranslator($translator);
    }

    public function homeAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-home');
        $rows = $this->table->getAll();
        $view->rows = $rows;
        return $view;
    }

    public function addAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-add');
        $allLanguages = array_slice($this->getConfig()->toArray()['Languages'], 1);
        $view->langs = $allLanguages;
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $view;
        }

        $bcid = $this->table->getLastBcId() + 1;
        //$bcid++;

        $language = $this->params()->fromPost('bc-lang');
        $message = $this->params()->fromPost('bc-message');
        $type = $this->params()->fromPost('bc-type');
        $expireDate = $this->params()->fromPost('bc-expireDate');

        $notEmpty = false;

        for ($i = 0; $i < count($allLanguages); ++$i) {
            $len = strlen(strip_tags($message[$i]));
            $notEmpty |= ($len > 0);
        }

        if (!$notEmpty) {
            $view->error = true;
            return $view;
        }

        for ($i = 0; $notEmpty && $i < sizeof($allLanguages); $i++) {
            $this->saveRow(
                $bcid,
                $language[$i],
                $message[$i],
                $type,
                $expireDate
            );
        }
        return $this->redirect()->toRoute('broadcastadmin', ['action' => 'home']);
    }

    /* saves a single row to the table */
    private function saveRow($bcid, $language, $message, $type, $expireDate)
    {
        $row = $this->table->createRow();
        $row->bcid = $bcid;
        $row->language = $language;
        $row->message = $message;
        $row->type = $type;
        $row->expireDate = $expireDate;
        $row->save();
    }
    public function previewAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-preview');
        $bcid = $this->params()->fromRoute('bcid');
        $lang = $this->getTranslatorLocale();
        $row = $this->table->getBroadcast($bcid, $lang);

        $view->row = $row;
        /*$DateConverter = new Converter();       // How to get/set timezone TODO view timezone
        $view->cDate = $DateConverter->convert('Y-m-d H:i', 'd.m.Y  H:i', $view->row->createDate);
        $view->modDate = isset($row->changeDate) ? $DateConverter->convert('Y-m-d H:i', 'd.m.Y  H:i', $view->row->changeDate) : '';
        */
        return $view;
    }


}
