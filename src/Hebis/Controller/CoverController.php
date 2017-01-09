<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 06.01.17
 * Time: 13:50
 */

namespace Hebis\Controller;


class CoverController extends \VuFind\Controller\CoverController
{

    /**
     * Get the cover loader object
     *
     * @return Loader
     */
    protected function getLoader()
    {
        // Construct object for loading cover images if it does not already exist:
        if (!$this->loader) {
            $cacheDir = $this->getCacheDir();
            $this->loader = new Loader(
                $this->getConfig(),
                $this->getServiceLocator()->get('VuFind\ContentCoversPluginManager'),
                $this->getServiceLocator()->get('VuFindTheme\ThemeInfo'),
                $this->getServiceLocator()->get('VuFind\Http')->createClient(),
                $cacheDir
            );
            \VuFind\ServiceManager\Initializer::initInstance(
                $this->loader, $this->getServiceLocator()
            );
        }
        return $this->loader;
    }
}