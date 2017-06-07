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

namespace Hebis\RecordTab;

use VuFind\RecordDriver\AbstractBase as AbstractRecordDriver;


/**
 * Class PluginManager
 * @package Hebis\RecordTab
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class PluginManager extends \VuFind\RecordTab\PluginManager
{


    /**
     * Get an array of valid tabs for the provided record driver.
     *
     * @param AbstractRecordDriver $driver Record driver
     * @param array $config Tab configuration (map of
     * driver class => tab configuration)
     * @param \Zend\Http\Request $request User request (optional)
     *
     * @return array               service name => tab object
     */
    public function getTabsForRecord(
        AbstractRecordDriver $driver,
        array $config,
        $request = null
    ) {
        $tabs = [];
        foreach ($this->getTabServiceNames($driver, $config) as $tabKey => $svc) {
            if (!$this->has($svc)) {
                continue;
            }
            $newTab = $this->get($svc);
            if (method_exists($newTab, 'setRecordDriver')) {
                $newTab->setRecordDriver($driver);
            }
            if ($request instanceof \Zend\Http\Request
                && method_exists($newTab, 'setRequest')
            ) {
                $newTab->setRequest($request);
            }
            if ($newTab->isActive()) {
                $tabs[$tabKey] = $newTab;
            }
        }
        return $tabs;
    }

    /**
     * Convenience method to load tab information, including default, in a
     * single pass. Returns an associative array with 'tabs' and 'default' keys.
     *
     * @param AbstractRecordDriver $driver Record driver
     * @param array $config Tab configuration (map of
     * driver class => tab configuration)
     * @param \Zend\Http\Request $request User request (optional)
     * @param string $fallback Fallback default tab to use if no
     * tab specified or matched.
     *
     * @return array
     */
    public function getTabDetailsForRecord(
        AbstractRecordDriver $driver,
        array $config,
        $request = null,
        $fallback = null
    ) {
        $tabs = $this->getTabsForRecord($driver, $config, $request);
        $default = $this->getDefaultTabForRecord($driver, $config, $tabs, $fallback);
        return compact('tabs', 'default');
    }
}