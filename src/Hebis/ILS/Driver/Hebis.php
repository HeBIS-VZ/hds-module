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


namespace Hebis\ILS\Driver;

use HAB\Pica\Record\CopyRecord;
use HAB\Pica\Record\Field;
use HAB\Pica\Record\LocalRecord;
use HAB\Pica\Record\SubField;
use HAB\Pica\Record\TitleRecord;
use Hebis\RecordDriver\PicaRecord;
use Hebis\RecordDriver\SolrMarc;
use League\OAuth2\Client\Provider\GenericProvider;
use Zend\Session\SessionManager;
use VuFind\Exception\ILS as ILSException;


class Hebis extends PAIA
{

    public function __construct(\VuFind\Date\Converter $converter, \Zend\Session\SessionManager $sessionManager)
    {
        parent::__construct($converter, $sessionManager);
    }


    public function init()
    {
        parent::init();

        if (!isset($this->config['PAIA']['client_id']) || !isset($this->config['PAIA']['client_secret'])) {
            throw new ILSException('PAIA/client_id AND PAIA/client_secret configuration needs to be set.');
        }

        $this->provider = new GenericProvider([
            'clientId' => $this->config['PAIA']['client_id'],
            // The client ID assigned to you by the provider
            'clientSecret' => $this->config['PAIA']['client_secret'],
            // The client password assigned to you by the provider
            'redirectUri' => 'http://sbpc2.hebis.uni-frankfurt.de/vufind2/oauth/callback',
            'urlAuthorize' => $this->config['PAIA']['baseUrl'] . 'oauth/v2/auth',
            'urlAccessToken' => $this->config['PAIA']['baseUrl'] . 'oauth/v2/token',
            'urlResourceOwnerDetails' => $this->config['PAIA']['baseUrl'] . 'core/',
            'scopes' => 'read_patron read_fees read_items write_items',
        ]);
    }

    /*
    public function getHolding($id, array $patron = null)
    {
        parent::getHolding($id, $patron);
    }
    */
    /**
     * Parse an array with DAIA status information.
     *
     * @param string $id Record id for the DAIA array.
     * @param array $daiaArray Array with raw DAIA status information.
     *
     * @return array            Array with VuFind compatible status information.
     */
    protected function parseDaiaArray($id, $daiaArray)
    {
        $picaRecord = SolrMarc::getCurrentPicaRecord();
        $doc_id = null;


        //$this->
        $doc_href = null;
        if (isset($daiaArray['id'])) {
            $doc_id = $daiaArray['id'];
        }

        if (isset($daiaArray['href'])) {
            // url of the document (not needed for VuFind)
            $doc_href = $daiaArray['href'];
        }

        if (isset($daiaArray['message'])) {
            // log messages for debugging
            $this->logMessages($daiaArray['message'], 'document');
        }

        $epn = trim(str_replace("epn:", "", $daiaArray['item'][0]['id']));

        /** @var LocalRecord $picaLevel2 */
        $picaLevel2 = array_filter($picaRecord->getLocalRecords(), function ($localRecord) use ($epn) {
            /** @var LocalRecord $localRecord */
            $copyRecords = $localRecord->getCopyRecords();
            /** @var CopyRecord $copyRecord */
            foreach ($copyRecords as $copyRecord) {
                $epn_c = trim($copyRecord->getEPN());
                if ($epn_c == $epn) {
                    return true;
                }
            }
            return false;
        });
        // if one or more items exist, iterate and build result-item
        if (isset($daiaArray['item']) && is_array($daiaArray['item'])) {
            $number = 0;
            foreach ($daiaArray['item'] as $item) {
                $result_item = [];
                $result_item['id'] = $id;
                $result_item['item_id'] = $item['id'];
                // custom DAIA field used in getHoldLink()
                $result_item['ilslink'] = (isset($item['href']) ? $item['href'] : $doc_href);
                // count items
                $number++;
                $result_item['number'] = $this->getItemNumber($item, $number);
                // set default value for barcode
                $result_item['barcode'] = $this->getItemBarcode($item);
                // set default value for reserve
                $result_item['reserve'] = $this->getItemReserveStatus($item);
                // get callnumber
                $result_item['callnumber'] = $this->getItemCallnumber($item);
                // get location
                $result_item['location'] = $this->getItemLocation($item);
                // get location link
                $result_item['locationhref'] = $this->getItemLocationLink($item);
                // status and availability will be calculated in own function
                $result_item = $this->getItemStatus($item) + $result_item;
                // add result_item to the result array
                $result_item['addLink'] = true;
                $result_item['is_holdable'] = true;
                $result_item['check'] = false;
                $result_item['addStorageRetrievalRequestLink'] = true;
                $result_item['doc_id'] = $doc_id;

                if (isset($picaLevel2[0])) {
                    /** @var CopyRecord $copyRecord */
                    $copyRecord = $picaLevel2[0]->getCopyRecords()[0];
                    /** @var Field $field */
                    $field = $copyRecord->getFirstMatchingField("209G");
                    if (!empty($field)) {
                        /** @var SubField $subField */
                        $subField = $field->getNthSubField("x", 0);

                        if ("00" == trim($subField->getValue())) {
                            /** @var SubField $volNoSubField */
                            $volNoSubField = $field->getNthSubField("a", 0);
                            $result_item['doc_id'] = trim($volNoSubField->getValue());
                        }
                    }
                }
                //$copyRecord->
                $result[] = $result_item;
            } // end iteration on item
        }

        return $result;
    }


    public function checkRequestIsValid($id, $data, $patron)
    {
        return true; //TODO: implement a validation
    }


    public function getPickUpLocations($patron = null, $holdDetails = null)
    {

        return [
            ["locationID" => 3, "locationDisplay" => "Zentralbibliothek (ZB): Ausleihe (EG)"],
            ["locationID" => 50, "locationDisplay" => "ZB: Lesesaal Geisteswissenschaften (EG)"],
            ["locationID" => 51, "locationDisplay" => "ZB: Asienbibliothek (1.Stock)"],
            ["locationID" => 52, "locationDisplay" => "ZB: Lesesaal Naturwissenschaften (2.Stock)"],
            ["locationID" => 53, "locationDisplay" => "ZB: Lesesaal Spezialsammlungen (3. Stock)"]
        ];
    }

    /**
     * @return TitleRecord
     */
    private function getPicaRecord()
    {
        $session = new \Zend\Session\Container('Record', $this->sessionManager);
        return $session->picaRecord;
    }

    protected function getItemLocation($item)
    {
        $location = '';

        if (isset($item['department'])
            && isset($item['department']['content'])
        ) {
            $location .= (empty($location)
                ? $item['department']['content']
                : ' - ' . $item['department']['content']);
        }

        if (isset($item['storage'])
            && isset($item['storage']['content'])
        ) {
            $location .= (empty($location)
                ? $item['storage']['content']
                : ' - ' . $item['storage']['content']);
        }

        return (empty($location) ? 'Unknown' : $location);
    }

    /**
     * Returns the value for "location" href in VuFind getStatus/getHolding array
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemLocationLink($item)
    {
        return isset($item['storage']['href'])
            ? $item['storage']['href'] : false;
    }
}
