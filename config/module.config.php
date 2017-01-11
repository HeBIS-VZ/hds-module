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
            'db_table' => [
                'abstract_factories' => ['VuFind\Db\Table\PluginFactory'],
                'factories' => [
                    'user_oauth' => 'Hebis\Db\Table\Factory::getUserOAuth'
                ]
            ],
            'ils_driver' => [

                'factories' => [
                    'hebis' => 'Hebis\ILS\Driver\Factory::getHebis',
                ]
            ],
            'autocomplete' => [
                'factories' => [
                    'solrterms' => function($sm) {
                        return new \Hebis\Autocomplete\Terms(
                            $sm->getServiceLocator()->get('VuFind\SearchResultsPluginManager'),
                            $sm->getServiceLocator()->get('VuFind\Config')->get('searches')
                        );
                    }
                ]
            ],
        ],

    ],
    'service_manager' => [
        'factories' => [
            'VuFind\Translator' => 'Hebis\Service\Factory::getTranslator',
            'VuFind\RecordDriverPluginManager' => 'Hebis\RecordDriver\Factory::getRecordDriverPluginManager',
            'Zend\Session\SessionManager' => 'Zend\Session\Service\SessionManagerFactory',
            'Zend\Session\Config\ConfigInterface' => 'Zend\Session\Service\SessionConfigFactory',
            'VuFind\WorldCatUtils' => 'Hebis\Service\Factory::getWorldCatUtils',
            'VuFind\AutocompletePluginManager' => 'VuFind\Service\Factory::getAutocompletePluginManager',
            'VuFind\SearchResultsPluginManager' => 'VuFind\Service\Factory::getSearchResultsPluginManager',
        ],
        'invokables' => [
            'VuFind\Terms' => 'Hebis\Search\Service',
        ]
    ],

    'session_config' => [
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ],

    'controllers' => [
        'factories' => [
            'OAuth' => function(ServiceManager $sm) {
                $oauthController = new \Hebis\Controller\OAuthController();
                $oauthController->setServiceLocator($sm->getServiceLocator());
                $oauthController->init();
                return $oauthController;
            },
            'recordfinder' => function(ServiceManager $sm) {
                return new \Hebis\Controller\RecordFinderController(
                    $sm->getServiceLocator()->get('VuFind\Config')->get('config')
                );
            }
        ],
        'invokables' => [
            'my-research' => 'Hebis\Controller\MyResearchController',
        ]
    ],
    'router' => [
        'routes' => [
            'oauth' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/oauth/callback[/]',
                    'defaults' => [
                        'controller' => 'OAuth',
                        'action'     => 'Callback',
                   ],
                ],
            ],
            'oauth-token-renew' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/oauth/renew[/]',
                    'defaults' => [
                        'controller'    => 'OAuth',
                        'action'        => 'renew'
                    ]
                ]
            ],
        ],
    ],
];

$recordRoutes = ['recordfinder' => 'RecordFinder'];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);


return $config;
