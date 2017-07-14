<?php


namespace Hebis\Controller;

use VuFindAdmin\Controller\AbstractAdmin;
use Hebis\Form\Add;

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
        $form = new Add();

//        if($this->getRequest()->isPost()) {
//            $form->setData($this->getRequest()->getPost());
//
//            //TODO Save page into Data bank
//        }

        $view = $this->createViewModel(array('form' => $form));
        $view->setTemplate('custompages/add');

        return $view;
    }



}