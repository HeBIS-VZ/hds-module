<?php

namespace Hebis\View\Helper\Root;


use Hebis\Db\Table\StaticPost;
use VuFind\I18n\Translator\TranslatorAwareTrait;
use Zend\View\Helper\AbstractHelper;

/**
 * class for static pages navigation
 *
 * @package Hebis\View\Helper\Root
 */
class StaticPagesNavigation extends AbstractHelper
{

    use TranslatorAwareTrait;

    protected $table;

    public function __construct(StaticPost $table, $translator)
    {
        $this->table = $table;
        $this->setTranslator($translator);
    }


    public function __invoke()
    {
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getNav()
    {
        $arr = array();

        $staticPagesList = $this->table->getNav();

        if (sizeof($staticPagesList) == 0)
            $arr[] = [
                "pid" => 0,
                "title" => $this->translate('No Page Found'),
                "url" => ''
            ];
        else
            foreach ($staticPagesList as $page) {
                $url = $this->getView()->url('home') . 'Staticpages/View/' . $page->uid;
                $arr[] = [
                    "pid" => $page->pid,
                    "title" => $page->nav_title,
                    "url" => $url
                ];
        }
        return $arr;
    }
}