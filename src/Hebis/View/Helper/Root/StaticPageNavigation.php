<?php
/**
 * Created by PhpStorm.
 * User: rosh
 * Date: 16.10.17
 * Time: 11:45
 */

namespace Hebis\View\Helper\Root;


class StaticPageNavigation extends \Zend\View\Helper\AbstractHelper
{

    private $table;

    public function __construct($table)
    {

    }


    public function __invoke()
    {
        return $this;
    }

    public function getNav()
    {
        return "Navigation";
    }
}