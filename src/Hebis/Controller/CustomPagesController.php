<?php


namespace Hebis\Controller;

use VuFind\SMS\AbstractBase;
use VuFindAdmin\Controller\AbstractAdmin;
use Hebis\Db\Table\StaticPost;

/**
 * Class to manage custom pages
 *
 * @package Controller
 */
class CustomPagesController extends AbstractAdmin
{


    /**
     * Custom Pages Manager Details
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {

        $view = $this->createViewModel();
        $view->setTemplate('custompages/home');

        $table = $this->getTable('static_post');

        $rows = $table->fetchAll();

        $view->rows = $rows;

        return $view;
    }

    public function viewPage($id)
    {
        $view = $this->createViewModel();
        $view->setTemplate('custompages/viewpage');

        return $view;
    }

    public function addPageAction($params)
    {

        $view = $this->createViewModel();
        $view->setTemplate('custompages/add');

        return $view;
    }

    public function editPageAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('custompages/edit');

        return $view;
    }

    public function deletePageAction()
    {
        $view = $this->createViewModel();
        $view->setTemplate('custompages/delete');

        return $view;

    }

}