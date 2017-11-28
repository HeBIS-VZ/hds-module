<?php

namespace Hebis\View\Helper\Root;

use VuFind\Search\Base\Options;
use Zend\Config\Config;
use Zend\View\Helper\AbstractHelper;

class SearchHandler extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function __invoke($searchClass)
    {
        return $this->view->translate($this->config[$searchClass]);
    }
}