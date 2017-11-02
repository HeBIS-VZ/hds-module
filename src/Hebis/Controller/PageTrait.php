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
trait PageTrait
{

    /**
     * @param $pid
     * @param $lang
     * @param null $template
     * @return mixed
     */
    protected function prepareViewStaticPages($pid, $lang, $template = null)
    {
        $view = $this->createViewModel();
        if (!empty($template)) {
            $view->setTemplate($template);
        }
        $rowSet = $this->table->getPostByPid($pid, $lang);
        $view->row = $rowSet->current();
        $DateConverter = new Converter();       // How to get/set timezone TODO view timezone
        $view->cDate = $DateConverter->convert('Y-m-d H:i', 'd.m.Y  H:i', $view->row->createDate);
        $view->modDate = isset($row->changeDate) ? $DateConverter->convert('Y-m-d H:i', 'd.m.Y  H:i', $view->row->changeDate) : '';
        return $view;
    }
}