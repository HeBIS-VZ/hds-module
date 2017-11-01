<?php

namespace Hebis\Controller;

use Hebis\Db\Table\StaticPost;
use VuFind\Controller\AbstractBase;

/**
 * Class controls navigation menu of static pages
 * @package Hebis\Controller
 */
class StaticPagesController extends AbstractBase
{

    use StaticPagesTrait;

    /**
     * @var StaticPost
     */
    protected $table;

    public function __construct(StaticPost $table)
    {
        $this->table = $table;
    }

    /** Returns a view for static page with user template
     * @return \Zend\View\Model\ViewModel
     */
    public function vvviewAction()
    {
        return $this->prepareViewStaticPages('staticpages/view');
    }

}