<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 17.01.17
 * Time: 16:28
 */

namespace Hebis\Autocomplete;


class Factory
{

    public static function getTerms($sm)
    {
        return new \Hebis\Autocomplete\Terms(
            $sm->getServiceLocator()->get('VuFind\SearchResultsPluginManager'),
            $sm->getServiceLocator()->get('VuFind\Config')->get('searches')
        );
    }
}