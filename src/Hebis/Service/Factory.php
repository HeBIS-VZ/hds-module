<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 30.03.16
 * Time: 14:27
 */

namespace Hebis\Service;


use Zend\ServiceManager\ServiceManager;

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
        $factory = new \Zend\Mvc\Service\TranslatorServiceFactory();
        $translator = $factory->createService($sm);

        // Set up the ExtendedIni plugin:
        $config = $sm->get('VuFind\Config')->get('config');

        //global i18n files located in vendor folder
        $additionalGlobalLangFolders = $config->LanguageConfiguration->additional_global_language_folders;
        $globalLangFolderArr = explode(',',$additionalGlobalLangFolders);
        array_walk($globalLangFolderArr, function(&$item, $key) {
            $item = sprintf("%s/%s", APPLICATION_PATH . '/vendor', $item);
        });

        //local i18n files located in local folder
        $additionalLocalLangFolders = $config->LanguageConfiguration->additional_local_language_folders;
        $localLangFolderArr = explode(',',$additionalLocalLangFolders);
        array_walk($localLangFolderArr, function(&$item, $key) {
            $item = sprintf("%s/%s", LOCAL_OVERRIDE_DIR, $item);
        });

        $pathStack = array_merge([APPLICATION_PATH  . '/languages'], $globalLangFolderArr, $localLangFolderArr);

        $fallbackLocales = $config->Site->language == 'en'
            ? 'en'
            : [$config->Site->language, 'en'];

        try {
            $pm = $translator->getPluginManager();
        } catch (\Zend\Mvc\Exception\BadMethodCallException $ex) {
            // If getPluginManager is missing, this means that the user has
            // disabled translation in module.config.php or PHP's intl extension
            // is missing. We can do no further configuration of the object.
            return $translator;
        }
        $pm->setService(
            'extendedini',
            new \VuFind\I18n\Translator\Loader\ExtendedIni(
                $pathStack, $fallbackLocales
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

}