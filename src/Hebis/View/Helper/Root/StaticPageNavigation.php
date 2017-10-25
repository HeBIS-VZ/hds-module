<?php
/**
 * Created by PhpStorm.
 * User: rosh
 * Date: 16.10.17
 * Time: 11:45
 */


namespace Hebis\View\Helper\Root;


use Hebis\Db\Table\StaticPost;
use VuFind\I18n\Translator\TranslatorAwareTrait;


class StaticPageNavigation extends \Zend\View\Helper\AbstractHelper
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
            $arr[] = ["pid" => 0, "title" => $this->translate('No Page Found')];

        else foreach ($staticPagesList as $page) {
            $arr[] = ["pid" => $page->pid, "title" => $page->nav_title];
        }

        return $arr;
    }
}