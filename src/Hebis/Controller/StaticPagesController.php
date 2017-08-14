<?php


namespace Hebis\Controller;

use VuFindAdmin\Controller\AbstractAdmin;


/**
 * Class to manage static pages
 *
 * @package Hebis\Controller
 * @author Roshak Zarhoun <roshak.zarhoun@stud.tu-darmstadt.de>
 */
class StaticPagesController extends AbstractAdmin
{


    /**
     * Static Pages Administrator Home View
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {

        $view = $this->createViewModel();
        $view->setTemplate('staticpages/home');
        $table = $this->getTable('static_post');

        $view->rows = $table->getAll();

        return $view;
    }

    /** Action: view static page by route
     * @return \Zend\View\Model\ViewModel
     */
    public function viewAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('staticpages/view');
        $table = $this->getTable('static_post');
        $id = $this->params()->fromRoute();
        $row = $table->getPost($id);
        $visible = $row->visible;
        $view->row = $row;

        return $view;
    }

    /** Action: Add new static page
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {

        $view = $this->createViewModel();
        $view->setTemplate('staticpages/add');
        // TODO: create form

        return $view;
    }

    public function editPageAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('staticpages/edit');

        return $view;
    }

    public function deletePageAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('staticpages/delete');
        // TODO: Delete query

        return $view;

    }

}