<?php


namespace Hebis\Controller;

use VuFind\Date\Converter;
use Zend\View\Model\ViewModel;

/**
 * Trait StaticPagesTrait
 * prepares some reusable methods for controllers
 *
 * @package Hebis\Controller
 * @author Roshak Zarhoun <roshakz@gmail.com>
 */
trait StaticPagesTrait
{

    /**
     * @param string $template Path to view template
     * @return ViewModel
     */
    protected function prepareViewStaticPages($template)
    {
        $view = $this->createViewModel();
        $view->setTemplate($template);
        $uid = $this->params()->fromRoute();
        $row = $this->table->getPost($uid);
        $view->row = $row;
        $visible = $row->visible;
        $DateConverter = new Converter();       // How to get/set timezone TODO view timezone
        $view->cDate = $DateConverter->convert('Y-m-d H:i', 'd.m.Y  H:i', $row->createDate);
        $view->modDate = isset($row->changeDate) ? $DateConverter->convert('Y-m-d H:i', 'd.m.Y  H:i', $row->changeDate) : '---';
        return $view;
    }
}