<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 24.01.17
 * Time: 14:57
 */

namespace Hebis\Search\Results;

use \Hebis\Search\Solr\Results as Solr;
use Zend\ServiceManager\ServiceManager;

class Factory
{

    /**
     * Factory for Solr results object.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Solr
     */
    public static function getSolr(ServiceManager $sm)
    {
        $factory = new PluginFactory();
        $solr = $factory->createServiceWithName($sm, 'solr', 'Solr');

        $params = $sm->getServiceLocator()->get('VuFind\SearchParamsPluginManager')->get('Solr');
        $class = ""; //$this->getClassName($name, $requestedName);
        $solr = new Solr($params);


        $config = $sm->getServiceLocator()
            ->get('VuFind\Config')->get('config');
        $spellConfig = isset($config->Spelling)
            ? $config->Spelling : null;
        $solr->setSpellingProcessor(
            new \VuFind\Search\Solr\SpellingProcessor($spellConfig)
        );

        return $solr;
    }
}