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

namespace Hebis\RecordDriver;

use Hebis\RecordDriver\PicaRecord;
use VuFind\RecordDriver\AbstractBase;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Session\Container as SessionContainer;

class PluginManager extends \VuFind\RecordDriver\PluginManager implements ServiceLocatorAwareInterface
{
    /**
     * Convenience method to retrieve a populated Solr record driver.
     *
     * @param array $data Raw Solr data
     *
     * @return AbstractBase
     */
    public function getSolrRecord($data)
    {
        if (isset($data['recordtype'])) {
            $key = 'Solr' . ucwords($data['recordtype']);
            $recordType = $this->has($key) ? $key : 'SolrDefault';
        } else {
            $recordType = 'SolrDefault';
        }

        // Build the object:
        /** @var \Hebis\RecordDriver\SolrMarc $driver */
        $driver = $this->get($recordType);
        $driver->setRawData($data);
        $config = $this->serviceLocator->get('VuFind\Config')->get('config');

        if (isset($config['Hebis']['driver_cache']) && $config['Hebis']['driver_cache'] === 'session') {
            return $this->initDriver($driver, $data);
        }

        $picaRecord = $this->initPica($data);

        $driver->setPicaRecord($picaRecord); //set Pica Record

        return $driver;
    }

    private function getSession()
    {
        static $session = false;
        if (!$session) {
            $session = new SessionContainer(get_class($this));
        }
        return $session;
    }

    /**
     * @param \Hebis\RecordDriver\SolrMarc $driver
     * @param array $data
     * @return \Hebis\RecordDriver\SolrMarc
     */
    private function initDriver(SolrMarc $driver, array $data)
    {
        $session = $this->getSession();

        $picaStorage = $session->getDefaultManager()->getStorage();

        if (!empty($picaStorage)) {
            if ($picaStorage[$data['id']] instanceof PicaRecord) {
                $pica = $picaStorage[$data['id']];
                $driver->setPicaRecord($pica);
                return $driver;
            }
        }
        $picaRecord = $this->initPica($data);
        $driver->setPicaRecord($picaRecord); //set Pica Record
        //save PicaRecord in Session
        $picaStorage[$data['id']] = $picaRecord;

        return $driver;
    }

    /**
     * @param array $data
     * @return PicaRecord
     */
    private function initPica(array $data)
    {
        //parse PICA
        $parser = PicaRecordParser::getInstance();
        $picaRecord = $parser->parse($data['raw_fullrecord'])->getRecord();

        return $picaRecord;
    }
}
