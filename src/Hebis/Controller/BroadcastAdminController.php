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

    public function bchomeAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('broadcastadmin/bc-home');
        $rows = $this->table->getAll();
        $view->rows = $rows;
        return $view;
    }

    public function addAction()
    {
        // TODO
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