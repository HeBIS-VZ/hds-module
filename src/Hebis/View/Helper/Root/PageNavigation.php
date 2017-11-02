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
class PageNavigation extends AbstractHelper
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

        foreach ($staticPagesList as $page) {
            $url = $this->getView()->url('home') . 'Page/Show?pid=' . $page->pid;
            $arr[] = [
                "pid" => $page->pid,
                "nav_title" => $page->nav_title,
                "headline" => $page->headline,
                "url" => $url
            ];
        }

        return $arr;
    }
}