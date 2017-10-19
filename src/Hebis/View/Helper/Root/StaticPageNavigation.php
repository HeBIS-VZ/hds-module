<?php
/**
 * Created by PhpStorm.
 * User: rosh
 * Date: 16.10.17
 * Time: 11:45
 */


namespace Hebis\View\Helper\Root;


use Hebis\Db\Table\StaticPost;

class StaticPageNavigation extends \Zend\View\Helper\AbstractHelper
{

    protected $table;

    public function __construct(StaticPost $table)
    {
        $this->table = $table;
    }


    public function __invoke()
    {
        return $this;
    }

    public function getNav()
    {
        $arr = [];
        $staticPagesList = $this->table->getNav();
        foreach ($staticPagesList as $page) {
            $pageArray = ["uid" => $page->id, "title" => $page->headline];
            $arr[] = $pageArray;
        }

        return $arr;
    }
}