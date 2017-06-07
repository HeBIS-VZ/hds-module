<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an 
 * extension of the open source library search engine VuFind, that 
 * allows users to search and browse beyond resources. More 
 * Information about VuFind you will find on http://www.vufind.org
 * 
 * Copyright (C) 2016 
 * Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
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


/**
 * Class DAIA
 * @package Hebis\ILS\Driver
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class DAIA extends \VuFind\ILS\Driver\DAIA
{

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
        $doc_id = null;
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

        $result = [];

        // if one or more items exist, iterate and build result-item
        if (isset($daiaArray['item']) && is_array($daiaArray['item'])) {

            foreach ($daiaArray['item'] as $key => $item) {
                $result_item = [];
                $result_item['id'] = $id;
                $result_item['item_id'] = $item['id'];
                // custom DAIA field used in getHoldLink()
                $result_item['ilslink'] = (isset($item['href']) ? $item['href'] : $doc_href);
                // count items

                $result_item['number'] = $this->getItemNumber($item, $key);
                // set default value for barcode
                $result_item['barcode'] = $this->getItemBarcode($item);
                // set default value for reserve
                $result_item['reserve'] = $this->getItemReserveStatus($item);
                // get callnumber
                $result_item['callnumber'] = $this->getItemCallnumber($item);
                // get location
                $result_item['location'] = $this->getItemLocation($item);
                // get location id
                $result_item['location_id'] = $this->getItemStorageId($item);
                // get location link
                $result_item['location_href'] = $this->getItemLocationLink($item);

                // comments
                if (array_key_exists('message', $item)) {
                    $result_item['message'] = $item['message'];
                }
                //availability information e.g. retro
                if (array_key_exists('available', $item)) {
                    $result_item['available'] = $item['available'][$key];
                }
                // status and availability will be calculated in own function
                $result_item = $this->getItemStatus($item) + $result_item;
                // add result_item to the result array
                $result[] = $result_item;
            } // end iteration on item
        }

        return $result;
    }


    protected function getItem($item)
    {
        $ret = '';

        if (isset($item['department']) && isset($item['department']['content'])) {
            $ret = $item['department']['content'];
        }

        if (isset($item['storage']) && isset($item['storage']['content'])) {
            $ret .= ' - ' . $item['storage']['content'];
        }

        return (empty($ret) ? 'Unknown' : $ret);
    }

    protected function getItemStorage($item)
    {
        $storage = '';

        if (isset($item['storage']) && isset($item['storage']['content'])) {
            $storage .= $item['storage']['content'];
        }

        return (empty($storage) ? 'Unknown' : $storage);
    }

}