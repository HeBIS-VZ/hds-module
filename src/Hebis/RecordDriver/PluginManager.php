<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 22.02.16
 * Time: 17:37
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