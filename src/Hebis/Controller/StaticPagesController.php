<?php


namespace Hebis\Controller;

use Hebis\Db\Table\StaticPost;
use VuFindAdmin\Controller\AbstractAdmin;


/**
 * Class to manage static pages
 *
 * @package Hebis\Controller
 * @author Roshak Zarhoun <roshak.zarhoun@stud.tu-darmstadt.de>
 */
class StaticPagesController extends AbstractAdmin
{
    protected $table;

    public function __construct(StaticPost $table)
    {
        $this->table = $table;
    }

    /**
     * Static Pages Administrator Home View
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {

        $view = $this->createViewModel();
        $view->setTemplate('adminstaticpages/list');

        $view->rows = $this->table->getAll();

        return $view;
    }

    /** Action: view static page by route
     * @return \Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('adminstaticpages/view');
        $id = $this->params()->fromRoute();
        $row = $this->table->getPost($id);
        $visible = $row->visible;
        $view->row = $row;

        return $view;
    }

    /** Action: Add new static page
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $request = $this->getRequest();

        $view = $this->createViewModel();
        $view->setTemplate('adminstaticpages/add');

        if (!$request->isPost()) {
            return $view;
        }
        $row = $this->table->createRow();
        $row->headline = $this->params()->fromPost('headline');
        $row->content = $this->params()->fromPost('content');
        $row->save();
        $id = $row->id;
        return $this->forwardTo('adminstaticpages', 'view', ['id' => $id]);
    }

    public function editAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('adminstaticpages/edit');

        return $view;
    }

    public function deleteAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('adminstaticpages/delete');
        // TODO: Delete query

        return $view;

    }

    public function initTable()
    {

    }

}