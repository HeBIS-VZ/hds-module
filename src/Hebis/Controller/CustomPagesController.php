<?php


namespace Hebis\Controller;

use VuFindAdmin\Controller\AbstractAdmin;

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

        return $view;
    }

    public function addPageAction()
    {

        $view = $this->createViewModel();
        $view->setTemplate('custompages/add');

        return $view;
    }


    public function saveAction()
    {
        //TODO
    }



}