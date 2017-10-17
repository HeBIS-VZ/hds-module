<?php

namespace Hebis\View\Helper;

class ControllerName extends \Zend\View\Helper\AbstractHelper
{
    protected $routeMatch;

    /**
     * ControllerName constructor.
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch
     */
    public function __construct($routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    public function __invoke()
    {
        if ($this->routeMatch) {
            $controller = $this->routeMatch->getParam('controller', 'index');
            return $controller;
        }
    }
}