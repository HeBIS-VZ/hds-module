<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an
 * extension of the open source library search engine VuFind, that
 * allows users to search and browse beyond resources. More
 * Information about VuFind you will find on http://www.vufind.org
 *
 * Copyright (C) 2016
 * HeBIS Verbundzentrale des HeBIS-Verbundes
 * Goethe-Universität Frankfurt / Goethe University of Frankfurt
 * http://www.hebis.de
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Hebis\Service;

use Hebis\RecordTab\PluginManager;
use VuFind\I18n\Translator\Loader\ExtendedIni;
use Zend\Mvc\Exception\BadMethodCallException;
use Zend\Mvc\Service\TranslatorServiceFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ServiceManager
 * @package Hebis\Service
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class Factory extends \VuFind\Service\Factory
{

    /**
     * Construct the translator.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \Zend\I18n\Translator\TranslatorInterface
     */
    public static function getTranslator(ServiceManager $sm)
    {
        $factory = new TranslatorServiceFactory();
        $translator = $factory->createService($sm);

        // Set up the ExtendedIni plugin:
        $config = $sm->get('VuFind\Config')->get('config');

        //global i18n files located in vendor folder
        $additionalGlobalLangFolders = $config->LanguageConfiguration->additional_vendor_language_folders;
        $globalLangFolderArr = explode(',', $additionalGlobalLangFolders);
        array_walk($globalLangFolderArr, function (&$item, $key) {
            $item = sprintf("%s/%s", APPLICATION_PATH . '/vendor', $item);
        });

        //local i18n files located in local folder
        $additionalLocalLangFolders = $config->LanguageConfiguration->additional_local_language_folders;
        $localLangFolderArr = explode(',', $additionalLocalLangFolders);
        array_walk($localLangFolderArr, function (&$item, $key) {
            $item = sprintf("%s/%s", LOCAL_OVERRIDE_DIR, $item);
        });

        $pathStack = array_merge([APPLICATION_PATH . '/languages'], $globalLangFolderArr, $localLangFolderArr);

        $fallbackLocales = $config->Site->language == 'en'
            ? 'en'
            : [$config->Site->language, 'en'];

        try {
            $pm = $translator->getPluginManager();
        } catch (BadMethodCallException $ex) {
            // If getPluginManager is missing, this means that the user has
            // disabled translation in module.config.php or PHP's intl extension
            // is missing. We can do no further configuration of the object.
            return $translator;
        }
        $pm->setService(
            'extendedini',
            new ExtendedIni(
                $pathStack,
                $fallbackLocales
            )
        );

        // Set up language caching for better performance:
        try {
            $translator->setCache(
                $sm->get('VuFind\CacheManager')->getCache('language')
            );
        } catch (\Exception $e) {
            // Don't let a cache failure kill the whole application, but make
            // note of it:
            $logger = $sm->get('VuFind\Logger');
            $logger->debug(
                'Problem loading cache: ' . get_class($e) . ' exception: '
                . $e->getMessage()
            );
        }

        return $translator;
    }

    /**
     * Construct the WorldCat helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \VuFind\Connection\WorldCatUtils
     */
    public static function getWorldCatUtils(ServiceManager $sm)
    {
        $config = $sm->get('VuFind\Config')->get('config');
        $client = $sm->get('VuFind\Http')->createClient();
        $ip = $sm->get('Request')->getServer()->get('SERVER_ADDR');

        return new \Hebis\Connection\WorldCatUtils(
            isset($config->WorldCat) ? $config->WorldCat : null,
            $client,
            true,
            $ip
        );
    }

    public static function getExport(ServiceManager $sm)
    {
        return new \Hebis\Export(
            $sm->get('VuFind\Config')->get('config'),
            $sm->get('VuFind\Config')->get('export')
        );
    }

    /**
     * Construct the RecordTab Plugin Manager.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return PluginManager
     */
    public static function getRecordTabPluginManager(ServiceManager $sm)
    {
        $configKey = "recordtab";
        $config = $sm->get('Config');
        return new PluginManager(
            new Config(
                $config['vufind']['plugin_managers'][$configKey]
            )
        );
    }

    /**
     * Construct the search memory helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \VuFind\Search\Memory
     */
    public static function getSearchMemory(ServiceManager $sm)
    {
        return new \Hebis\Search\Memory(
            new \Zend\Session\Container('Search', $sm->get('VuFind\SessionManager'))
        );
    }

    /**
     * Construct the Search\Options Plugin Manager.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \VuFind\Search\Options\PluginManager
     */
    public static function getSearchOptionsPluginManager(ServiceManager $sm)
    {

        $config = $sm->get('Config');
        $searchParams = $config['vufind']['plugin_managers']['search_options'];
        return new \Hebis\Search\Options\PluginManager(
            new \Zend\ServiceManager\Config(
                $searchParams
            )
        );
    }

    public static function getSearchParamsPluginManager(ServiceManager $sm)
    {

        $config = $sm->get('Config');
        $searchParams = $config['vufind']['plugin_managers']['search_params'];
        return new \Hebis\Search\Params\PluginManager(
            new \Zend\ServiceManager\Config(
                $searchParams
            )
        );
    }

}