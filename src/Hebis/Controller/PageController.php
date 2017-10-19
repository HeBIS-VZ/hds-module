<?php
/**
 * Created by PhpStorm.
 * User: rosh
 * Date: 19.10.17
 * Time: 14:11
 */

namespace Hebis\Controller;


use VuFind\Controller\AbstractBase;
use VuFind\I18n\Translator\TranslatorAwareTrait;

class PageController extends AbstractBase
{
    use TranslatorAwareTrait;

    protected $table;

    /**
     * PageController constructor.
     * @param $table
     */
    public function __construct($table, $translator)
    {
        $this->table = $table;
        $this->setTranslator($translator);

    }

    /** Staticpages home view for users
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('staticpages/sp-home');
        $view->rows = $this->table->getAll();
        return $view;
    }

    public function showAction()
    {
        $view = $this->createViewModel();
        $id = $this->params()->fromQuery('id');
        $row = $this->table->getPost($id);
        $visible = $row->visible;
        $view->row = $row;
        $view->title = $row->headline;

        return $view;
    }


}