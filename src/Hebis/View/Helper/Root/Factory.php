<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 31.01.17
 * Time: 12:14
 */

namespace Hebis\View\Helper\Root;

use Zend\ServiceManager\ServiceManager;

class Factory extends \VuFind\View\Helper\Root\Factory
{

    /**
     * Construct the Citation helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Citation
     */
    public static function getCitation(ServiceManager $sm)
    {
        $config = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
        $dateConverter = $sm->getServiceLocator()->get('VuFind\DateConverter');
        return new Citation($dateConverter, $config);
    }

    /**
     * Construct the Record helper.
     *
     * @param ServiceManager $sm
     * @return Record
     */
    public static function getRecord(ServiceManager $sm)
    {
        $helper = new Record(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config')
        );

        $helper->setCoverRouter(
            $sm->getServiceLocator()->get('VuFind\Cover\Router')
        );

        return $helper;
    }
}