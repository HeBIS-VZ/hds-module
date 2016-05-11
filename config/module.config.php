<?php
namespace Hebis\Module\Configuration;

use Zend\ServiceManager\ServiceManager;

$config = [
    'vufind' => [
        'plugin_managers' => [
            'recorddriver' => [
                'factories' => [
                    'solrmarc' => 'Hebis\RecordDriver\Factory::getSolrMarc',
                ]
            ],
            'ils_driver' => [
                'invokables' => [
                    'hebis' => 'Hebis\ILS\Driver\Hebis'
                ],
                'daia' => function(ServiceManager $sm) {
                    return new \Hebis\ILS\Driver\DAIA(
                        $sm->getServiceLocator()->get('VuFind\DateConverter')
                    );
                }
            ],
        ],

    ],
    'service_manager' => [
        'factories' => [
            'VuFind\Translator' => 'Hebis\Service\Factory::getTranslator',
            'VuFind\RecordDriverPluginManager' => 'Hebis\RecordDriver\Factory::getRecordDriverPluginManager',
            'Zend\Session\SessionManager' => 'Zend\Session\Service\SessionManagerFactory',
            'Zend\Session\Config\ConfigInterface' => 'Zend\Session\Service\SessionConfigFactory',
        ]
    ],

    'session_config' => [
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ],

];

return $config;
