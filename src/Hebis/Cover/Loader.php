<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an
 * extension of the open source library search engine VuFind, that
 * allows users to search and browse beyond resources. More
 * Information about VuFind you will find on http://www.vufind.org
 *
 * Copyright (C) 2017
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
     * @return void
     * @throws \Exception
     */
    protected function determineLocalFile($ids): void
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