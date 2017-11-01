<?php

namespace Hebis\Module\Configuration;


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
                    'user_oauth' => 'Hebis\Db\Table\Factory::getUserOAuth',
                    'static_post' => 'Hebis\Db\Table\Factory::getStaticPost'
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
            'staticpagesadmin' => 'Hebis\Controller\Factory::getStaticPagesAdminController',
            'staticpages' => 'Hebis\Controller\Factory::getStaticPagesController',
            'page' => 'Hebis\Controller\Factory::getPageController',
        ],
        'invokables' => [
            'ajax' => 'Hebis\Controller\AjaxController',
            'eds' => 'Hebis\Controller\EdsController',
            'my-research' => 'Hebis\Controller\MyResearchController',
            'search' => 'Hebis\Controller\SearchController',
            'adminlogs' => 'Hebis\Controller\AdminLogsController',
        ]
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
            'adminlogs' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/Admin/Logs',
                    'defaults' => [
                        'controller' => 'adminlogs',
                        'action' => 'home',
                    ],
                ],
            ],
            'staticpages' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/Staticpages',
                    'defaults' => [
                        'controller' => 'staticpages',
                        'action' => '',
                    ]
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'preview' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/View/:pid',
                            'defaults' => [
                                'action' => 'vvview'
                            ],
                            'constraints' => [
                                'pid' => '\d+'
                            ]
                        ]
                    ]
                ]
            ],
            'staticpagesadmin' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/Admin/Staticpages',
                    'defaults' => [
                        'controller' => 'staticpagesadmin',
                        'action' => 'list',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'preview' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/View/:pid',
                            'defaults' => [
                                'action' => 'preview'
                            ],
                            'constraints' => [
                                'pid' => '\d+'
                            ]
                        ]
                    ],
                    'add' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/Add',
                            'defaults' => [
                                'action' => 'add'
                            ]
                        ]
                    ],
                    'edit' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/Edit/:id',
                            'defaults' => [
                                'action' => 'edit'
                            ],
                            'constraints' => [
                                'id' => '\d+'
                            ]
                        ]
                    ],
                    'json' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/:id/Json/:method',
                            'defaults' => [
                                'action' => 'json',
                            ],
                            'constraints' => [
                                'id' => '\d+'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]

];

$recordRoutes = ['recordfinder' => 'RecordFinder'];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);

return $config;