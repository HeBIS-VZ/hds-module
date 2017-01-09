<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 06.01.17
 * Time: 13:51
 */

namespace Hebis\Cover;


use VuFind\Content\Covers\PluginManager as ApiManager;

class Loader extends \VuFind\Cover\Loader
{

    public function __construct(\Zend\Config\Config $config, ApiManager $manager, \VuFindTheme\ThemeInfo $theme, \Zend\Http\Client $client, $baseDir = null)
    {
        parent::__construct($config, $manager, $theme, $client, $baseDir);
    }

    /**
     * Support method for fetchFromAPI() -- set the localFile property.
     *
     * @param array $ids IDs returned by getIdentifiers() method
     *
     * @return void
     */
    protected function determineLocalFile($ids) : void
    {
        // We should check whether we have cached images for the 13- or 10-digit
        // ISBNs. If no file exists, we'll favor the 10-digit number if
        // available for the sake of brevity.
        if (isset($ids['isbn'])) {
            $file = $this->getCachePath($this->size, $ids['isbn']->get13());
            if (!is_readable($file) && $ids['isbn']->get10()) {
                return $this->getCachePath($this->size, $ids['isbn']->get10());
            }
            return $file;
        } else if (isset($ids['issn'])) {
            return $this->getCachePath($this->size, $ids['issn']);
        } else if (isset($ids['oclc'])) {
            return $this->getCachePath($this->size, 'OCLC' . $ids['oclc']);
        } else if (isset($ids['upc'])) {
            return $this->getCachePath($this->size, 'UPC' . $ids['upc']);
        }
        throw new \Exception('Unexpected code path reached!');
    }

    public function loadImage($settings = [])
    {
        // Load settings from legacy function parameters if they are not passed
        // in as an array:
        $settings = is_array($settings)
            ? array_merge($this->getDefaultSettings(), $settings)
            : $this->getImageSettingsFromLegacyArgs(func_get_args());


    }

    protected function getSettings()
    {

    }
}