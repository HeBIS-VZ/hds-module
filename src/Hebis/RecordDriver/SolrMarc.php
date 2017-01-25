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

use HAB\Pica\Record\Record as PicaRecord;
use Hebis\Cover\ContentType;
use VuFindSearch\Backend\Exception\BackendException;


/**
 * Model for MARC records in Solr.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
class SolrMarc extends \VuFind\RecordDriver\SolrMarc
{

    /**
     * @var PicaRecord
     */
    static protected $currentPicaRecord;

    /**
     * @return PicaRecord
     */
    public static function getCurrentPicaRecord()
    {
        return self::$currentPicaRecord;
    }


    /**
     * Extracts PPN (003@ $0) from a raw record
     *
     * @param $rawRecord
     * @return mixed
     */
    public static function extractPPN($rawRecord)
    {
        $array = explode("\n", trim($rawRecord['raw_fullrecord']));

        $ppnLine = array_filter($array, function ($item) {
            return !preg_match('/^003@/', $item);
        });

        $matches = [];

        $pattern = '^003@\ {4}\$0([\dX]+)$'; //find ppn in sub field '$0'
        preg_match($pattern, $ppnLine[0], $matches);

        return $matches[1];
    }


    /**
     * MARC record
     *
     * @var \File_MARC_Record
     */
    protected $marcRecord;

    /**
     * ILS connection
     *
     * @var \VuFind\ILS\Connection
     */
    protected $ils = null;

    /**
     * Hold logic
     *
     * @var \VuFind\ILS\Logic\Holds
     */
    protected $holdLogic;

    /**
     * Title hold logic
     *
     * @var \VuFind\ILS\Logic\TitleHolds
     */
    protected $titleHoldLogic;

    /**
     * @var PicaRecord
     */
    protected $picaRecord;


    /**
     * Set raw data to initialize the object.
     *
     * @param mixed $data Raw data representing the record; Record Model
     * objects are normally constructed by Record Driver objects using data
     * passed in from a Search Results object.  In this case, $data is a Solr record
     * array containing MARC data in the 'fullrecord' field.
     *
     * @return void
     * @throws \File_MARC_Exception
     */
    public function setRawData($data)
    {
        // Call the parent's set method...
        parent::setRawData($data);

        // Also process the MARC record:
        $marc = trim($data['fullrecord']);

        // check if we are dealing with MARCXML
        if (substr($marc, 0, 1) == '<') {
            $marc = new \File_MARCXML($marc, \File_MARCXML::SOURCE_STRING);
        } else {
            // When indexing over HTTP, SolrMarc may use entities instead of certain
            // control characters; we should normalize these:
            $marc = str_replace(
                ['#29;', '#30;', '#31;'], ["\x1D", "\x1E", "\x1F"], $marc
            );

            //$marc = str_replace(["<", ">"], ["&lt;", "&gt;"], $marc);


            $marc = new \File_MARC($marc, \File_MARC::SOURCE_STRING);
        }
        try {
            $picaParser = PicaRecordParser::getInstance();
            $this->picaRecord = $picaParser->parse($data['raw_fullrecord'])->getRecord();
            self::$currentPicaRecord = $this->picaRecord;
        } catch (\Exception $e) {
            /** @var  \Zend\Log\LoggerInterface$logger */
            //$logger = $this->getLogger();
            //$logger->err("Could not parse pica record ".$data['id']." in class ". __CLASS__ . ", line: " . __LINE__);
            throw new BackendException("Error parsing PICA", 0, $e);
        }
        $this->marcRecord = $marc->next();

        if (!$this->marcRecord) {
            throw new \File_MARC_Exception('Cannot Process MARC Record');
        }

    }

    /**
     * @param PicaRecord $picaRecord
     */
    public function setPicaRecord(PicaRecord $picaRecord)
    {
        $this->picaRecord = $picaRecord;
    }

    /**
     * @return PicaRecord
     */
    public function getPicaRecord()
    {
        return $this->picaRecord;
    }
    
    public function getPPN()
    {
        return $this->fields['id'];
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small')
    {

        $arr = parent::getThumbnail($size);
        $arr['contenttype'] = ContentType::getContentType($this);
        return $arr;
    }

    /**
     * Get an array of all ISBNs associated with the record (may be empty).
     *
     * @return array
     */
    public function getISBNs()
    {
        $isbns = [];
        $f020_ = $this->marcRecord->getFields('020');
        /** @var \File_MARC_Data_Field $f020 */
        foreach ($f020_ as $f020) {
            if (!empty($f020)) {
                if (!empty($a = $f020->getSubfield('a'))) {
                    $isbns[] = $a->getData();
                }
                if (!empty($z = $f020->getSubfield('z'))) {
                    $isbns[] = $z->getData();
                }
            }
        }

        /** @var \File_MARC_Data_Field $f776 */
        $f776 = $this->marcRecord->getField('776');

        if (!empty($f776) && $f776->getIndicator('1') === '1') {
            if (!empty($z = $f776->getSubfield('z'))) {
                $isbns[] = $z;
            }
        }
        return $isbns;
    }
}
