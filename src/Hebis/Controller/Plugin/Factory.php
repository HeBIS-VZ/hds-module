<?php

namespace Hebis\Controller\Plugin;
use Zend\ServiceManager\ServiceManager;
/**
 * Class Factory
 * @package Hebis\Controller\Plugin
 * @author
 */
class Factory
{

    /**
     * Construct the ResultScroller plugin.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return ResultScroller
     */
    public static function getResultScroller(ServiceManager $sm)
    {
        return new ResultScroller(
            new \Zend\Session\Container(
                'ResultScroller',
                $sm->getServiceLocator()->get('VuFind\SessionManager')
            )
        );
    }
}