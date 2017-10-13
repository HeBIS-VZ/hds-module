<?php
namespace Hebis\Module\Configuration;

use Zend\ServiceManager\ServiceManager;

$config = [
    'vufind' => [
        'plugin_managers' => [
            'recorddriver' => [
                'factories' => [
                    'solrmarc' => 'Hebis\RecordDriver\Factory::getSolrMarc',
                    'eds' => 'Hebis\RecordDriver\Factory::getEDS',
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
                    'solrterms' => 'Hebis\Autocomplete\Factory::getTerms',
                ]
            ],
            'search_results' => [
                'abstract_factories' => ['Hebis\Search\Results\PluginFactory'],
                'factories' => [
                    'favorites' => 'VuFind\Search\Results\Factory::getFavorites',
                    'solr' => 'Hebis\Search\Results\Factory::getSolr',
                ],
            ],
            'search_options' => [
                'abstract_factories' => ['Hebis\Search\Options\PluginFactory'],
                'factories' => [
                    'eds' => 'VuFind\Search\Options\Factory::getEDS',
                ],
            ],
            'search_params' => [
                'abstract_factories' => ['Hebis\Search\Params\PluginFactory'],
            ],
            'recommend' => [
                'factories' => [
                    'topfacets' => 'Hebis\Recommend\Factory::getTopFacets',
                ],
                'invokables' => [
                    'pubdatevisajax' => 'Hebis\Recommend\PubDateVisAjax',
                ],
            ],
            'recordtab' => [
                'abstract_factories' => ['VuFind\RecordTab\PluginFactory'],
                'factories' => [
                    'holdingsils' => 'Hebis\RecordTab\Factory::getHoldingsILS',
                ],
                'invokables' => [
                    'description' => 'Hebis\RecordTab\Description',
                    'staffviewmarc' => 'Hebis\RecordTab\StaffViewMARC',
                    'toc' => 'Hebis\RecordTab\TOC',
                ],
            ],
        ],
        'recorddriver_tabs' => [
            'VuFind\RecordDriver\SolrMarc' => [
                'tabs' => [
                    'Holdings' => 'HoldingsILS',
                    'Description' => 'Description',
                    'TOC' => 'TOC',
                    'UserComments' => null,
                    'Reviews' => null,
                    'Excerpt' => null,
                    'Preview' => null,
                    'HierarchyTree' => null,
                    'Map' => null,
                    'Similar' => null,
                    'Details' => 'StaffViewMARC',
                ],
                'defaultTab' => 'Holdings',
            ],
        ]

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
            'VuFind\RecordTabPluginManager' => 'Hebis\Service\Factory::getRecordTabPluginManager',
            'VuFind\Search\Memory' => 'Hebis\Service\Factory::getSearchMemory',
            'VuFind\SearchOptionsPluginManager' => 'Hebis\Service\Factory::getSearchOptionsPluginManager',
            'VuFind\SearchParamsPluginManager' => 'Hebis\Service\Factory::getSearchParamsPluginManager',
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
            'OAuth' => 'Hebis\Controller\Factory::getOAuth',
            'recordfinder' => 'Hebis\Controller\Factory::getRecordFinder',
            'Xisbn' => 'Hebis\Controller\Factory::getXisbn',
            'record' => 'Hebis\Controller\Factory::getRecordController',
        ],
        'invokables' => [
            'ajax' => 'Hebis\Controller\AjaxController',
            'eds' => 'Hebis\Controller\EdsController',
            'my-research' => 'Hebis\Controller\MyResearchController',
            'search' => 'Hebis\Controller\SearchController',
            'edsrecord' => 'Hebis\Controller\EdsrecordController',
            'adminlogs' => 'Hebis\Controller\AdminLogs'
        ]
    ],
    'controller_plugins' => [
        'factories' => [
            'result-scroller' => 'Hebis\Controller\Plugin\Factory::getResultScroller',
        ],
    ],
    'router' => [
        'routes' => [
            'oauth' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/oauth/callback[/]',
                    'defaults' => [
                        'controller' => 'OAuth',
                        'action' => 'Callback',
                    ],
                ],
            ],
            'oauth-token-renew' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/oauth/renew[/]',
                    'defaults' => [
                        'controller' => 'OAuth',
                        'action' => 'renew'
                    ]
                ]
            ],
            'ajax-xisbn' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/xisbn/xid[/]',
                    'defaults' => [
                        'controller' => 'Xisbn',
                        'action' => 'xid'
                    ]
                ]
            ],
            'logs' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/Admin/Logs',
                    'defaults' => [
                        'controller' => 'AdminLogs',
                        'action' => 'Home',
                    ]
                ]
            ],
        ],
    ],

];

$recordRoutes = ['recordfinder' => 'RecordFinder'];
//$ajaxRoutes = ['AJAX/XISBN'];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addStaticRoutes($config, ['EdsRecord/redilink']);

return $config;