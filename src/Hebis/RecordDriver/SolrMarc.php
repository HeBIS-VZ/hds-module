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

use HAB\Pica\Record\TitleRecord;
use VuFind\Exception\ILS as ILSException,
    VuFind\View\Helper\Root\RecordLink,
    VuFind\XSLT\Processor as XSLTProcessor,
    VuFindCode\ISBN;
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

    static protected $currentPicaRecord;

    /**
     * @return TitleRecord
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



    /*
     * @var array
     */
    //protected $picaTit;

    /*
     * @var array
     */
    //private $picaLocal;


    //private $picaExp;
    //private $picaEpn;
    //private $picaPpn;
    //private $picaILN;

    protected $mainAuthor = array();
    protected $secondaryAuthors = array();
    protected $partAuthors = array();
    protected $subjectHeadings = array();
    protected $corporation = array();
    protected $interpreter = array();
    protected $secondaryCategories = array();
    private $copies = array();
    private $urls = array();
    private $retrourl = array();
    private $levelonedata = array();
    private $series = array();
    private $reviewed = array();
    private $review = array();
    private $journal = array();
    private $jbibcontext = array();
    private $journalprepost = array();
    private $volumes = array();
    private $otherEditions = array();
    private $allTitleLinks = array();

    /**
     * Set raw data to initialize the object.
     *
     * @param mixed $data Raw data representing the record; Record Model
     * objects are normally constructed by Record Driver objects using data
     * passed in from a Search Results object.  In this case, $data is a Solr record
     * array containing MARC data in the 'fullrecord' field.
     *
     * @return void
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
     * @param TitleRecord $picaRecord
     */
    public function setPicaRecord(TitleRecord $picaRecord)
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
     * Process Pica-Record
     *
     * @param $record
     * @access private
     *
    public function processPicaRecord(&$record)
    {
        global $configArray;

        //$saved = !empty($_SESSION['pica']) && $_SESSION['pica']['recordId'] == $record['id'] ? true : false;

        $saved = !empty($this->session->offsetGet('SolrMarc'));

        if ($saved) {
            $this->picaTitleRecord = $_SESSION['pica']['picaRecord'];
        } else {
            $rawRecord = trim($record['raw_fullrecord']);
            $parser = PicaRecordParser::getInstance();
            $this->picaTitleRecord = $parser->parse($rawRecord)->getRecord();
        }

        // Partial arrays
        //$this->picaTit = $this->picaTitleRecord->getTit();
        //$this->picaLocal = $this->picaTitleRecord->getLocal();

        //$this->picaExp = $this->picaTitleRecord->getExp();

        // $this->secondaryCategories = $this->getSecondaryCategories();
        // Für Exemplardaten Epn und ILN falls eingestellt
        //$this->picaEpn = $this->picaTitleRecord->getEpn();
        //$this->picaPpn = $this->picaTitleRecord->getPpn();
        $this->picaILN = isset($configArray['HeBIS']['ILN']) ? $configArray['HeBIS']['ILN'] : false;

        if (!$saved) {
            // Process level 0 data


            /*
            $this->_processPicaTit();

            // Process level 1 data
            $this->_processPicaLocal();

            // Process level 2 data
            $this->_processPicaExp();
            *
            // Save results in session for the next running e.g. AJAX calls
            if ($_REQUEST['module'] == 'Record')

                $_SESSION['pica'] = $this->picaTitleRecord; //TODO: use ZF2 Session Handling

                /*
                $_SESSION['pica'] = array('recordId' => $record['id'],
                    'picaRecord' => $this->picaTitleRecord,
                    'copies' => $this->copies,
                    'urls' => $this->urls,
                    'retrourl' => $this->retrourl,
                    'levelonedata' => $this->levelonedata,
                    'series' => $this->series,
                    'reviewed' => $this->reviewed,
                    'review' => $this->review,
                    'journal' => $this->journal,
                    'jbibcontext' => $this->jbibcontext,
                    'journalprepost' => $this->journalprepost,
                    'volumes' => $this->volumes,
                    'otherEditions' => $this->otherEditions,
                    'allTitleLinks' => $this->allTitleLinks
                );
                *
        } else {
            $this->picaTitleRecord = $_SESSION['pica']; //TODO: use ZF2 Session Handling
            /*
            $this->copies = $_SESSION['pica']['copies'];
            $this->urls = $_SESSION['pica']['urls'];
            $this->retrourl = $_SESSION['pica']['retrourl'];
            $this->levelonedata = $_SESSION['pica']['levelonedata'];
            $this->series = $_SESSION['pica']['series'];
            $this->reviewed = $_SESSION['pica']['reviewed'];
            $this->review = $_SESSION['pica']['review'];
            $this->journal = $_SESSION['pica']['journal'];
            $this->jbibcontext = $_SESSION['pica']['jbibcontext'];
            $this->journalprepost = $_SESSION['pica']['journalprepost'];
            $this->volumes = $_SESSION['pica']['volumes'];
            $this->otherEditions = $_SESSION['pica']['otherEditions'];
            $this->allTitleLinks = $_SESSION['pica']['allTitleLinks'];
            *
        }

    }*/




    /**
     * Get access restriction notes for the record.
     *
     * @return array
     */
    public function getAccessRestrictions()
    {
        return $this->getFieldArray('506');
    }

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @return array
     */
    public function getAllSubjectHeadings()
    {
        // These are the fields that may contain subject headings:
        $fields = [
            '600', '610', '611', '630', '648', '650', '651', '653', '655', '656'
        ];

        // This is all the collected data:
        $retval = [];

        // Try each MARC field one at a time:
        foreach ($fields as $field) {
            // Do we have any results for the current field?  If not, try the next.
            $results = $this->marcRecord->getFields($field);
            if (!$results) {
                continue;
            }

            // If we got here, we found results -- let's loop through them.
            foreach ($results as $result) {
                // Start an array for holding the chunks of the current heading:
                $current = [];

                // Get all the chunks and collect them together:
                $subfields = $result->getSubfields();
                if ($subfields) {
                    foreach ($subfields as $subfield) {
                        // Numeric subfields are for control purposes and should not
                        // be displayed:
                        if (!is_numeric($subfield->getCode())) {
                            $current[] = $subfield->getData();
                        }
                    }
                    // If we found at least one chunk, add a heading to our result:
                    if (!empty($current)) {
                        $retval[] = $current;
                    }
                }
            }
        }

        // Send back everything we collected:
        return $retval;
    }

    /**
     * Get award notes for the record.
     *
     * @return array
     */
    public function getAwards()
    {
        return $this->getFieldArray('586');
    }

    /**
     * Get the bibliographic level of the current record.
     *
     * @return string
     */
    public function getBibliographicLevel()
    {
        $leader = $this->marcRecord->getLeader();
        $biblioLevel = strtoupper($leader[7]);

        switch ($biblioLevel) {
            case 'M': // Monograph
                return "Monograph";
            case 'S': // Serial
                return "Serial";
            case 'A': // Monograph Part
                return "MonographPart";
            case 'B': // Serial Part
                return "SerialPart";
            case 'C': // Collection
                return "Collection";
            case 'D': // Collection Part
                return "CollectionPart";
            default:
                return "Unknown";
        }
    }

    /**
     * Get notes on bibliography content.
     *
     * @return array
     */
    public function getBibliographyNotes()
    {
        return $this->getFieldArray('504');
    }

    /**
     * Get the main corporate author (if any) for the record.
     *
     * @return string
     */
    public function getCorporateAuthor()
    {
        // Try 110 first -- if none found, try 710 next.
        $main = $this->getFirstFieldValue('110', ['a', 'b']);
        if (!empty($main)) {
            return $main;
        }
        return $this->getFirstFieldValue('710', ['a', 'b']);
    }

    /**
     * Get notes on finding aids related to the record.
     *
     * @return array
     */
    public function getFindingAids()
    {
        return $this->getFieldArray('555');
    }

    /**
     * Get the first value matching the specified MARC field and subfields.
     * If multiple subfields are specified, they will be concatenated together.
     *
     * @param string $field The MARC field to read
     * @param array $subfields The MARC subfield codes to read
     *
     * @return string
     */
    protected function getFirstFieldValue($field, $subfields = null)
    {
        $matches = $this->getFieldArray($field, $subfields);
        return (is_array($matches) && count($matches) > 0) ?
            $matches[0] : null;
    }

    /**
     * Get general notes on the record.
     *
     * @return array
     */
    public function getGeneralNotes()
    {
        return $this->getFieldArray('500');
    }

    /**
     * Get human readable publication dates for display purposes (may not be suitable
     * for computer processing -- use getPublicationDates() for that).
     *
     * @return array
     */
    public function getHumanReadablePublicationDates()
    {
        return $this->getPublicationInfo('c');
    }

    /**
     * Get an array of newer titles for the record.
     *
     * @return array
     */
    public function getNewerTitles()
    {
        // If the MARC links are being used, return blank array
        $fieldsNames = isset($this->mainConfig->Record->marc_links)
            ? array_map('trim', explode(',', $this->mainConfig->Record->marc_links))
            : [];
        return in_array('785', $fieldsNames) ? [] : parent::getNewerTitles();
    }

    /**
     * Get the item's publication information
     *
     * @param string $subfield The subfield to retrieve ('a' = location, 'c' = date)
     *
     * @return array
     */
    protected function getPublicationInfo($subfield = 'a')
    {
        // First check old-style 260 field:
        $results = $this->getFieldArray('260', [$subfield]);

        // Now track down relevant RDA-style 264 fields; we only care about
        // copyright and publication places (and ignore copyright places if
        // publication places are present).  This behavior is designed to be
        // consistent with default SolrMarc handling of names/dates.
        $pubResults = $copyResults = [];

        $fields = $this->marcRecord->getFields('264');
        if (is_array($fields)) {
            foreach ($fields as $currentField) {
                $currentVal = $currentField->getSubfield($subfield);
                $currentVal = is_object($currentVal)
                    ? $currentVal->getData() : null;
                if (!empty($currentVal)) {
                    switch ($currentField->getIndicator('2')) {
                        case '1':
                            $pubResults[] = $currentVal;
                            break;
                        case '4':
                            $copyResults[] = $currentVal;
                            break;
                    }
                }
            }
        }
        if (count($pubResults) > 0) {
            $results = array_merge($results, $pubResults);
        } else if (count($copyResults) > 0) {
            $results = array_merge($results, $copyResults);
        }

        return $results;
    }

    /**
     * Get the item's places of publication.
     *
     * @return array
     */
    public function getPlacesOfPublication()
    {
        $fields = $this->marcRecord->getFields('260');
        $tmp = array();
        $base = $zusatz = '';
        $a = $b = $c = $e = $f = '';

        foreach ($fields as $field) {
            if (strcmp($field->getIndicator(1), '3') === 0) {
                $allSubfields = $field->getSubfields();

                foreach ($allSubfields as $currentSubfield) {
                    $code = trim($currentSubfield->getCode());
                    $data = trim($currentSubfield->getData());

                    if (strcmp($code, 'a') === 0) {
                        $a = $data;
                    }
                    if (strcmp($code, 'b') === 0) {
                        $b = $data;
                    }
                    if (strcmp($code, 'c') === 0) {
                        $c = $data;
                    }
                    if (strcmp($code, 'e') === 0) {
                        $e = $data;
                    }
                    if (strcmp($code, 'f') === 0) {
                        $f = $data;
                    }

                }

                $base = $a;

                if (strlen($b) > 0) {
                    if (strlen($base) > 0) {
                        $base = $base . ' : ';
                    }
                    $base = $base . $b;
                }
                if (strlen($c) > 0) {
                    if (strpos($c, ',') !== false) {
                        $occurrence = strpos($c, ',');
                        $c = substr($c, $occurrence + 1);
                    }
                    if (strlen($base) > 0) {
                        $base = $base . ', ';
                    }
                    $base = $base . $c;
                }
                if (strlen($e) > 0) {
                    $zusatz = $zusatz . $e;
                }
                if (strlen($f) > 0) {
                    if (strlen($zusatz) > 0) {
                        $zusatz = $zusatz . ', ';
                    }
                    $zusatz = $zusatz . $f;
                }
                if (strlen($zusatz) > 0) {
                    $zusatz = '(' . $zusatz . ')';
                }
                $tmp[] = array($base, $zusatz);
                $base = $zusatz = '';
                $a = $b = $c = $e = $f = '';
            }

        }
        return $tmp;
    }

    /**
     * Get an array of playing times for the record (if applicable).
     *
     * @return array
     */
    public function getPlayingTimes()
    {
        $times = $this->getFieldArray('306', ['a'], false);

        // Format the times to include colons ("HH:MM:SS" format).
        for ($x = 0; $x < count($times); $x++) {
            $times[$x] = substr($times[$x], 0, 2) . ':' .
                substr($times[$x], 2, 2) . ':' .
                substr($times[$x], 4, 2);
        }

        return $times;
    }

    /**
     * Get an array of previous titles for the record.
     *
     * @return array
     */
    public function getPreviousTitles()
    {
        // If the MARC links are being used, return blank array
        $fieldsNames = isset($this->mainConfig->Record->marc_links)
            ? array_map('trim', explode(',', $this->mainConfig->Record->marc_links))
            : [];
        return in_array('780', $fieldsNames) ? [] : parent::getPreviousTitles();
    }

    /**
     * Get credits of people involved in production of the item.
     *
     * @return array
     */
    public function getProductionCredits()
    {
        return $this->getFieldArray('508');
    }

    /**
     * Get an array of publication frequency information.
     *
     * @return array
     */
    public function getPublicationFrequency()
    {
        return $this->getFieldArray('310', ['a', 'b']);
    }

    /**
     * Get an array of strings describing relationships to other items.
     *
     * @return array
     */
    public function getRelationshipNotes()
    {
        return $this->getFieldArray('580');
    }

    /**
     * Get an array of all series names containing the record.  Array entries may
     * be either the name string, or an associative array with 'name' and 'number'
     * keys.
     *
     * @return array
     */
    /*public function getSeries()
    {
        $matches = [];

        // First check the 440, 800 and 830 fields for series information:
        $primaryFields = [
            '440' => ['a', 'p'],
            '800' => ['a', 'b', 'c', 'd', 'f', 'p', 'q', 't'],
            '830' => ['a', 'p']];
        $matches = $this->getSeriesFromMARC($primaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Now check 490 and display it only if 440/800/830 were empty:
        $secondaryFields = ['490' => ['a']];
        $matches = $this->getSeriesFromMARC($secondaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Still no results found?  Resort to the Solr-based method just in case!
        return parent::getSeries();
    }
    */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Support method for getSeries() -- given a field specification, look for
     * series information in the MARC record.
     *
     * @param array $fieldInfo Associative array of field => subfield information
     * (used to find series name)
     *
     * @return array
     */
    protected function getSeriesFromMARC($fieldInfo)
    {
        $matches = [];

        // Loop through the field specification....
        foreach ($fieldInfo as $field => $subfields) {
            // Did we find any matching fields?
            $series = $this->marcRecord->getFields($field);
            if (is_array($series)) {
                foreach ($series as $currentField) {
                    // Can we find a name using the specified subfield list?
                    $name = $this->getSubfieldArray($currentField, $subfields);
                    if (isset($name[0])) {
                        $currentArray = ['name' => $name[0]];

                        // Can we find a number in subfield v?  (Note that number is
                        // always in subfield v regardless of whether we are dealing
                        // with 440, 490, 800 or 830 -- hence the hard-coded array
                        // rather than another parameter in $fieldInfo).
                        $number
                            = $this->getSubfieldArray($currentField, ['v']);
                        if (isset($number[0])) {
                            $currentArray['number'] = $number[0];
                        }

                        // Save the current match:
                        $matches[] = $currentArray;
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Get an array of summary strings for the record.
     *
     * @return array
     */
    public function getSummary()
    {
        return $this->getFieldArray('520');
    }

    /**
     * Get an array of technical details on the item represented by the record.
     *
     * @return array
     */
    public function getSystemDetails()
    {
        return $this->getFieldArray('538');
    }

    /**
     * Get an array of note about the record's target audience.
     *
     * @return array
     */
    public function getTargetAudienceNotes()
    {
        return $this->getFieldArray('521');
    }

    /**
     * Get the text of the part/section portion of the title.
     *
     * @return string
     */
    public function getTitleSection()
    {
        return $this->getFirstFieldValue('245', ['n', 'p']);
    }

    /**
     * Get the statement of responsibility that goes with the title (i.e. "by John
     * Smith").
     *
     * @return string
     */
    public function getTitleStatement()
    {
        return $this->getFirstFieldValue('245', ['c']);
    }

    /**
     * Get an array of lines from the table of contents.
     *
     * @return array
     */
    public function getTOC()
    {
        // Return empty array if we have no table of contents:
        $fields = $this->marcRecord->getFields('505');
        if (!$fields) {
            return [];
        }

        // If we got this far, we have a table -- collect it as a string:
        $toc = [];
        foreach ($fields as $field) {
            $subfields = $field->getSubfields();
            foreach ($subfields as $subfield) {
                // Break the string into appropriate chunks,  and merge them into
                // return array:
                $toc = array_merge($toc, explode('--', $subfield->getData()));
            }
        }
        return $toc;
    }

    /**
     * Get hierarchical place names (MARC field 752)
     *
     * Returns an array of formatted hierarchical place names, consisting of all
     * alpha-subfields, concatenated for display
     *
     * @return array
     */
    public function getHierarchicalPlaceNames()
    {
        $placeNames = [];
        if ($fields = $this->marcRecord->getFields('752')) {
            foreach ($fields as $field) {
                $subfields = $field->getSubfields();
                $current = [];
                foreach ($subfields as $subfield) {
                    if (!is_numeric($subfield->getCode())) {
                        $current[] = $subfield->getData();
                    }
                }
                $placeNames[] = implode(' -- ', $current);
            }
        }
        return $placeNames;
    }

    /**
     * Return an array of associative URL arrays with one or more of the following
     * keys:
     *
     * <li>
     *   <ul>desc: URL description text to display (optional)</ul>
     *   <ul>url: fully-formed URL (required if 'route' is absent)</ul>
     *   <ul>route: VuFind route to build URL with (required if 'url' is absent)</ul>
     *   <ul>routeParams: Parameters for route (optional)</ul>
     *   <ul>queryString: Query params to append after building route (optional)</ul>
     * </li>
     *
     * @return array
     */
    public function getURLs()
    {
        $retVal = [];

        // Which fields/subfields should we check for URLs?
        $fieldsToCheck = [
            '856' => ['y', 'z'],   // Standard URL
            '555' => ['a']         // Cumulative index/finding aids
        ];

        foreach ($fieldsToCheck as $field => $subfields) {
            $urls = $this->marcRecord->getFields($field);
            if ($urls) {
                foreach ($urls as $url) {
                    // Is there an address in the current field?
                    $address = $url->getSubfield('u');
                    if ($address) {
                        $address = $address->getData();

                        // Is there a description?  If not, just use the URL itself.
                        foreach ($subfields as $current) {
                            $desc = $url->getSubfield($current);
                            if ($desc) {
                                break;
                            }
                        }
                        if ($desc) {
                            $desc = $desc->getData();
                        } else {
                            $desc = $address;
                        }

                        $retVal[] = ['url' => $address, 'desc' => $desc];
                    }
                }
            }
        }

        return $retVal;
    }

    /**
     * Get all record links related to the current record. Each link is returned as
     * array.
     * Format:
     * array(
     *        array(
     *               'title' => label_for_title
     *               'value' => link_name
     *               'link'  => link_URI
     *        ),
     *        ...
     * )
     *
     * @return null|array
     */
    public function getAllRecordLinks()
    {
        // Load configurations:
        $fieldsNames = isset($this->mainConfig->Record->marc_links)
            ? explode(',', $this->mainConfig->Record->marc_links) : [];
        $useVisibilityIndicator
            = isset($this->mainConfig->Record->marc_links_use_visibility_indicator)
            ? $this->mainConfig->Record->marc_links_use_visibility_indicator : true;

        $retVal = [];
        foreach ($fieldsNames as $value) {
            $value = trim($value);
            $fields = $this->marcRecord->getFields($value);
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    // Check to see if we should display at all
                    if ($useVisibilityIndicator) {
                        $visibilityIndicator = $field->getIndicator('1');
                        if ($visibilityIndicator == '1') {
                            continue;
                        }
                    }

                    // Get data for field
                    $tmp = $this->getFieldData($field);
                    if (is_array($tmp)) {
                        $retVal[] = $tmp;
                    }
                }
            }
        }
        return empty($retVal) ? null : $retVal;
    }

    /**
     * Support method for getFieldData() -- factor the relationship indicator
     * into the field number where relevant to generate a note to associate
     * with a record link.
     *
     * @param File_MARC_Data_Field $field Field to examine
     *
     * @return string
     */
    protected function getRecordLinkNote($field)
    {
        // Normalize blank relationship indicator to 0:
        $relationshipIndicator = $field->getIndicator('2');
        if ($relationshipIndicator == ' ') {
            $relationshipIndicator = '0';
        }

        // Assign notes based on the relationship type
        $value = $field->getTag();
        switch ($value) {
            case '780':
                if (in_array($relationshipIndicator, range('0', '7'))) {
                    $value .= '_' . $relationshipIndicator;
                }
                break;
            case '785':
                if (in_array($relationshipIndicator, range('0', '8'))) {
                    $value .= '_' . $relationshipIndicator;
                }
                break;
        }

        return 'note_' . $value;
    }

    /**
     * Returns the array element for the 'getAllRecordLinks' method
     *
     * @param \File_MARC_Data_Field $field Field to examine
     *
     * @return array|bool                 Array on success, boolean false if no
     * valid link could be found in the data.
     */
    protected function getFieldData($field)
    {
        // Make sure that there is a t field to be displayed:
        if ($title = $field->getSubfield('t')) {
            $title = $title->getData();
        } else {
            return false;
        }

        //TODO: check if that really works
        $linkTypeSetting = isset($this->mainConfig->Record->marc_links_link_types)
            ? $this->mainConfig->Record->marc_links_link_types
            : 'id,oclc,dlc,isbn,issn,title';
        $linkTypes = explode(',', $linkTypeSetting);
        $linkFields = $field->getSubfields('w');

        // Run through the link types specified in the config.
        // For each type, check field for reference
        // If reference found, exit loop and go straight to end
        // If no reference found, check the next link type instead
        foreach ($linkTypes as $linkType) {
            switch (trim($linkType)) {
                case 'oclc':
                    foreach ($linkFields as $current) {
                        if ($oclc = $this->getIdFromLinkingField($current, 'OCoLC')) {
                            $link = ['type' => 'oclc', 'value' => $oclc];
                        }
                    }
                    break;
                case 'dlc':
                    foreach ($linkFields as $current) {
                        if ($dlc = $this->getIdFromLinkingField($current, 'DLC', true)) {
                            $link = ['type' => 'dlc', 'value' => $dlc];
                        }
                    }
                    break;
                case 'id':
                    foreach ($linkFields as $current) {
                        if ($bibLink = $this->getIdFromLinkingField($current)) {
                            $link = ['type' => 'bib', 'value' => $bibLink];
                        }
                    }
                    break;
                case 'isbn':
                    if ($isbn = $field->getSubfield('z')) {
                        $link = [
                            'type' => 'isn', 'value' => trim($isbn->getData()),
                            'exclude' => $this->getUniqueId()
                        ];
                    }
                    break;
                case 'issn':
                    if ($issn = $field->getSubfield('x')) {
                        $link = [
                            'type' => 'isn', 'value' => trim($issn->getData()),
                            'exclude' => $this->getUniqueId()
                        ];
                    }
                    break;
                case 'title':
                    $link = ['type' => 'title', 'value' => $title];
                    break;
            }
            // Exit loop if we have a link
            if (isset($link)) {
                break;
            }
        }
        // Make sure we have something to display:
        return !isset($link) ? false : [
            'title' => $this->getRecordLinkNote($field),
            'value' => $title,
            'link' => $link
        ];
    }

    /**
     * Returns an id extracted from the identifier subfield passed in
     *
     * @param \File_MARC_Subfield $idField MARC field containing id information
     * @param string $prefix Prefix to search for in id field
     * @param bool $raw Return raw match, or normalize?
     *
     * @return string|bool                 ID on success, false on failure
     */
    protected function getIdFromLinkingField($idField, $prefix = null, $raw = false)
    {
        $text = $idField->getData();
        if (preg_match('/\(([^)]+)\)(.+)/', $text, $matches)) {
            // If prefix matches, return ID:
            if ($matches[1] == $prefix) {
                // Special case -- LCCN should not be stripped:
                return $raw
                    ? $matches[2]
                    : trim(str_replace(range('a', 'z'), '', ($matches[2])));
            }
        } else if ($prefix == null) {
            // If no prefix was given or found, we presume it is a raw bib record
            return $text;
        }
        return false;
    }

    /**
     * Get Status/Holdings Information from the internally stored MARC Record
     * (support method used by the NoILS driver).
     *
     * @param array $field The MARC Field to retrieve
     * @param array $data A keyed array of data to retrieve from subfields
     *
     * @return array
     */
    public function getFormattedMarcDetails($field, $data)
    {
        // Initialize return array
        $matches = [];
        $i = 0;

        // Try to look up the specified field, return empty array if it doesn't
        // exist.
        $fields = $this->marcRecord->getFields($field);
        if (!is_array($fields)) {
            return $matches;
        }

        // Extract all the requested subfields, if applicable.
        foreach ($fields as $currentField) {
            foreach ($data as $key => $info) {
                $split = explode("|", $info);
                if ($split[0] == "msg") {
                    if ($split[1] == "true") {
                        $result = true;
                    } elseif ($split[1] == "false") {
                        $result = false;
                    } else {
                        $result = $split[1];
                    }
                    $matches[$i][$key] = $result;
                } else {
                    // Default to subfield a if nothing is specified.
                    if (count($split) < 2) {
                        $subfields = ['a'];
                    } else {
                        $subfields = str_split($split[1]);
                    }
                    $result = $this->getSubfieldArray(
                        $currentField, $subfields, true
                    );
                    $matches[$i][$key] = count($result) > 0
                        ? (string)$result[0] : '';
                }
            }
            $matches[$i]['id'] = $this->getUniqueID();
            $i++;
        }
        return $matches;
    }

    /**
     * Return an XML representation of the record using the specified format.
     * Return false if the format is unsupported.
     *
     * @param string $format Name of format to use (corresponds with OAI-PMH
     * metadataPrefix parameter).
     * @param string $baseUrl Base URL of host containing VuFind (optional;
     * may be used to inject record URLs into XML when appropriate).
     * @param RecordLink $recordLink Record link helper (optional; may be used to
     * inject record URLs into XML when appropriate).
     *
     * @return mixed         XML, or false if format unsupported.
     */
    public function getXML($format, $baseUrl = null, $recordLink = null)
    {
        // Special case for MARC:
        if ($format == 'marc21') {
            $xml = $this->marcRecord->toXML();
            $xml = str_replace(
                [chr(27), chr(28), chr(29), chr(30), chr(31)], ' ', $xml
            );
            $xml = simplexml_load_string($xml);
            if (!$xml || !isset($xml->record)) {
                return false;
            }

            // Set up proper namespacing and extract just the <record> tag:
            $xml->record->addAttribute('xmlns', "http://www.loc.gov/MARC21/slim");
            $xml->record->addAttribute(
                'xsi:schemaLocation',
                'http://www.loc.gov/MARC21/slim ' .
                'http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd',
                'http://www.w3.org/2001/XMLSchema-instance'
            );
            $xml->record->addAttribute('type', 'Bibliographic');
            return $xml->record->asXML();
        }

        // Try the parent method:
        return parent::getXML($format, $baseUrl, $recordLink);
    }

    /**
     * Attach an ILS connection and related logic to the driver
     *
     * @param \VuFind\ILS\Connection $ils ILS connection
     * @param \VuFind\ILS\Logic\Holds $holdLogic Hold logic handler
     * @param \VuFind\ILS\Logic\TitleHolds $titleHoldLogic Title hold logic handler
     *
     * @return void
     */
    public function attachILS(\VuFind\ILS\Connection $ils,
                              \VuFind\ILS\Logic\Holds $holdLogic,
                              \VuFind\ILS\Logic\TitleHolds $titleHoldLogic
    )
    {
        $this->ils = $ils;
        $this->holdLogic = $holdLogic;
        $this->titleHoldLogic = $titleHoldLogic;
    }

    /**
     * Do we have an attached ILS connection?
     *
     * @return bool
     */
    protected function hasILS()
    {
        return null !== $this->ils;
    }

    /**
     * Get an array of information about record holdings, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHoldings()
    {
        return $this->hasILS() ? $this->holdLogic->getHoldings(
            $this->getUniqueID(), $this->getConsortialIDs()
        ) : [];
    }

    /**
     * Get an array of information about record history, obtained in real-time
     * from the ILS.
     *
     * @return array
     */
    public function getRealTimeHistory()
    {
        // Get Acquisitions Data
        if (!$this->hasILS()) {
            return [];
        }
        try {
            return $this->ils->getPurchaseHistory($this->getUniqueID());
        } catch (ILSException $e) {
            return [];
        }
    }

    /**
     * Get a link for placing a title level hold.
     *
     * @return mixed A url if a hold is possible, boolean false if not
     *
    public function getRealTimeTitleHold()
    {
        if ($this->hasILS()) {
            $biblioLevel = strtolower($this->getBibliographicLevel());
            if ("monograph" == $biblioLevel || strstr("part", $biblioLevel)) {
                if ($this->ils->getTitleHoldsMode() != "disabled") {
                    return $this->titleHoldLogic->getHold($this->getUniqueID());
                }
            }
        }

        return false;
    }
    */
    /**
     * Returns true if the record supports real-time AJAX status lookups.
     *
     * @return bool
     */
    public function supportsAjaxStatus()
    {
        return true;
    }

    /**
     * Get access to the raw File_MARC object.
     *
     * @return File_MARCBASE
     */
    public function getMarcRecord()
    {
        return $this->marcRecord;
    }

    /**
     * Get an XML RDF representation of the data in this record.
     *
     * @return mixed XML RDF data (empty if unsupported or error).
     */
    public function getRDFXML()
    {
        return XSLTProcessor::process(
            'record-rdf-mods.xsl', trim($this->marcRecord->toXML())
        );
    }

    /**
     * Return the list of "source records" for this consortial record.
     *
     * @return array
     */
    public function getConsortialIDs()
    {
        return $this->getFieldArray('035', 'a', true);
    }

    /**
     * Get the full title of the record.
     *
     * @return string
     * @access public
     *
    public function getTitle()
    {
        // 245 $a
        $tmp = '';
        $tmp1 = $this->getFieldArray('245', 'a', false);
        if (count($tmp1) > 0) {
            $tmp = $tmp1[0];
        }

        // Sortierzeichen weg
        if (strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }

        return $tmp;
    }
    */

    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     * @access public
     */
    public function getShortTitle()
    {
        // 245 $a_:_$b
        $tmp = array();
        $tmp1 = $this->getFieldArray('245', array('a'), false);
        if (count($tmp1) > 0) {
            $tmp = $tmp1[0];
        }

        $tmp2 = $this->getFieldArray('245', array('b'), false);
        if (count($tmp2) > 0) {
            $tmp = $tmp . ' : ' . $tmp2[0];
        }

        // Sortierzeichen weg
        if (strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }

        return $tmp;
    }

    /**
     * Get the subtitle of the record.
     *
     * @return string
     * @access public
     */
    public function getSubtitle()
    {
        // 245 $b_/_$c
        $tmp = '';
        $tmp1 = $this->getFieldArray('245', array('h'), false);
        if (count($tmp1) > 0) {
            $tmp = $tmp1[0];
        }

        $tmp2 = $this->getFieldArray('245', array('b'), false);
        if (count($tmp2) > 0) {
            if (strlen($tmp) > 0) {
                $tmp = $tmp . ' : ';
            }
            $tmp = $tmp . $tmp2[0];
        }

        $tmp3 = $this->getFieldArray('245', array('c'), false);
        if (count($tmp3) > 0) {
            if (strlen($tmp) > 0) {
                $tmp = $tmp . ' / ';
            }
            $tmp = $tmp . $tmp3[0];
        }

        // Sortierzeichen weg
        if (strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }

        return $tmp;
    }

    /**
     * Get an array of all ISBNs associated with the record (may be empty).
     *
     * @return array
     * @access public
     */
    public function getISBNs()
    {
        $tmp1 = $this->getFieldArray('020', array('9'));
        $tmp2 = $this->getFieldArray('020', array('a'));

        foreach ($tmp1 as &$t) {
            $isbn = str_replace('-', '', $t);
            foreach ($tmp2 as $secondary) {
                if (preg_match('/sekundärausgabe/i', $secondary) && (strpos($secondary, $isbn) !== false)) {
                    $t .= ' (Sekundärausgabe)';
                    break;
                }
            }
        }
        return $tmp1;
    }

    /**
     * Return the first valid ISBN found in the record (favoring ISBN-10 over
     * ISBN-13 when possible).
     *
     * @return mixed
     */
    public function getCleanISBN()
    {
        // Get all the ISBNs and initialize the return value:
        $isbns = $this->getISBNs();
        $isbn13 = false;
        // Loop through the ISBNs:
        foreach ($isbns as $isbn) {
            // Strip off any unwanted notes:
            if ($pos = strpos($isbn, ' ')) {
                $isbn = substr($isbn, 0, $pos);
            }

            // If we find an ISBN-10, return it immediately; otherwise, if we find
            // an ISBN-13, save it if it is the first one encountered.
            $isbnObj = new ISBN($isbn);
            if ($isbn10 = $isbnObj->get10()) {
                return $isbn10;
            }
            if (!$isbn13) {
                $isbn13 = $isbnObj->get13();
            }
        }
        return $isbn13;
    }

    /**
     * Get an array of all ISSNs associated with the record (may be empty).
     *
     * @return array
     * @access public
     */
    public function getISSNs()
    {
        $tmp1 = $this->getFieldArray('022', array('a'));
        $tmp2 = $this->getFieldArray('022', array('y'));
        $tmp3 = $this->getFieldArray('029', array('a'));
        return array_merge($tmp1, $tmp2, $tmp3);
    }


    /**
     * Get the edition of the current record.
     *
     * @return string
     * @access public
     */
    public function getEdition()
    {
        return $this->getFirstFieldValue('250', array('a'));

    }

    /**
     * Get the Verlauf (tracking?) of the current record.
     *
     * @return string
     * @access public
     */
    public function getTracking()
    {
        return $this->getFieldArray('362', array('a'));
    }

    /**
     * Get the scale of the current record.
     *
     * @return string
     * @access public
     */
    public function getScale()
    {
        return $this->getFirstFieldValue('255', array('a'));
    }

    /**
     * Get the extent of the current record.
     *
     * @return string
     * @access public
     */
    public function getExtent()
    {
        // a : b ; c + e
        $tmp = array('a' => $this->getFirstFieldValue('300', array('a')),
            'b' => $this->getFirstFieldValue('300', array('b')),
            'c' => $this->getFirstFieldValue('300', array('c')),
            'e' => $this->getFirstFieldValue('300', array('e')));
        $extent = !empty($tmp['a']) ? (!empty($tmp['b']) ? $tmp['a'] . ' : ' . $tmp['b'] : $tmp['a']) : '';
        if ($extent != '') {
            $extent = !empty($tmp['c']) ? $extent . ' ; ' . $tmp['c'] : $extent;
            $extent = !empty($tmp['e']) ? $extent . ' + ' . $tmp['e'] : $extent;
        }
        return $extent;
    }

    /**
     * Get an array of all the languages associated with the record.
     *
     * @return array
     * @access public
     */
    public function getLanguages()
    {
        $tmp = array();
        $fields = $this->marcRecord->getFields('041');
        foreach ($fields as $field) {
            $allSubfields = $field->getSubfields();
            foreach ($allSubfields as $currentSubfield) {
                $code = trim($currentSubfield->getCode());
                $data = trim($currentSubfield->getData());
                if (strcmp($code, 'a') === 0) {
                    $tmp[] = $data;
                }

            }
        }
        return $tmp;
    }

    /**
     * Assign necessary Smarty variables and return a template name
     * to load in order to display the requested citation format.
     * For legal values, see getCitationFormats().  Returns null if
     * format is not supported.
     *
     * @param string $format Citation format to display.
     *
     * @return string        Name of Smarty template file to display.
     * @access public
     */
    public function getCitation($format)
    {
        // Build author list:
        $authors = $primary = $secondary = array();
        $primary = $this->getPrimaryAuthor();
        if (!empty($primary)) {
            $authors[] = $primary['name'];
        }

        $secondary_tmp = $this->getSecondaryAuthors();
        if (!empty($secondary_tmp)) {
            foreach ($secondary_tmp as $arr) {
                $secondary[] = $arr['name'];
            }
        }

        $authors = array_unique(
            array_merge($authors, $secondary)
        );

        // Collect all details for citation builder:
        $publishers = $this->getPublishers();
        $pubDates = $this->getPublicationDates();
        $pubPlaces = $this->getPlacesOfPublication();
        $details = array(
            'authors' => $authors,
            'title' => $this->getShortTitle(),
            'subtitle' => $this->getSubtitle(),
            'pubPlace' => count($pubPlaces) > 0 ? $pubPlaces[0] : null,
            'pubName' => count($publishers) > 0 ? $publishers[0] : null,
            'pubDate' => count($pubDates) > 0 ? $pubDates[0] : null,
            'edition' => array($this->getEdition())
        );

        // Build the citation:
        $citation = new CitationBuilder($details);
        if (in_array($format, $citation->getSupportedCitationFormats())) {
            return $citation->getCitation($format);
        } else {
            return '';
        }
    }

    /**
     * Get an array of all secondary authors (complementing getPrimaryAuthor()).
     *
     * @return array
     * @access public
     */
    public function getSecondaryAuthors()
    {
        $authors = array();
        $tmp2 = '';
        $fields = $this->marcRecord->getFields('700');

        // Extract all the requested subfields, if applicable.
        /**
         * @var  $key
         * @var \File_MARC_Data_Field $currentField
         */
        foreach ($fields as $key => $currentField) {
            /** @var array $allSubfields */
            $allSubfields = $currentField->getSubfields();
            $s0 = array();
            $a = '';
            $b = '';
            $c = '';
            $s4 = '';
            /** @var \File_MARC_Subfield $currentSubfield */
            foreach ($allSubfields as $currentSubfield) {
                $code = trim($currentSubfield->getCode());
                $data = trim($currentSubfield->getData());
                if (strcmp($code, '0') === 0) {
                    $s0[] = $data;
                }
                if (strcmp($code, 'a') === 0) {
                    $a = $data;
                }
                if (strcmp($code, 'b') === 0) {
                    $b = $data;
                }
                if (strcmp($code, 'c') === 0) {
                    $c = $data;
                }
                if (strcmp($code, '4') === 0) {
                    $s4 = $data;
                }
            }
            if (strcmp($s4, 'aut') === 0) {
                $tmp2 = $a;

                if (strlen($b) > 0) {
                    $tmp2 = $tmp2 . " " . $b;
                }
                if (strlen($c) > 0) {
                    $tmp2 = $tmp2 . " <" . $c . ">";
                }
                $authors[$key]['name'] = $tmp2;

                if (!empty($s0)) {
                    if (strpos($s0[0], '(DE-588)') !== false) {
                        $gnd = str_replace('(DE-588)', '', $s0[0]);
                        $authors[$key]['gnd'] = $gnd;
                    } else if (strpos($s0[0], '(DE-603)') !== false) {
                        $ppn = str_replace('(DE-603)', '', $s0[0]);
                        $authors[$key]['ppn'] = $ppn;
                    }
                }
            }
            $s0 = array();
            $a = '';
            $b = '';
            $c = '';
            $s4 = '';
            $tmp2 = '';
            $gnd = '';
            $ppn = '';
        }

        return $authors;
    }

    /**
     * Get the main author of the record.
     *
     * @return array
     * @access public
     */
    public function getPrimaryAuthor()
    {
        $author = array();

        $tmp = trim($this->getFirstFieldValue('100', array('a')));
        if (strlen(trim($this->getFirstFieldValue('100', array('b')))) > 0) {
            $tmp = $tmp . " " . trim($this->getFirstFieldValue('100', array('b')));
        }
        if (strlen(trim($this->getFirstFieldValue('100', array('c')))) > 0) {
            $tmp = $tmp . " <" . trim($this->getFirstFieldValue('100', array('c'))) . ">";
        }

        /*
        $author['name'] = $tmp;

        $authorid = trim($this->getFirstFieldValue('100', array('0')));
        if (!empty($authorid)) {
            list($gnd, $ppn) = explode('(DE-603)', str_replace('(DE-588)', '', $authorid));
            if (!empty($gnd)) {
                $author['gnd'] = trim($gnd);
            } else {
                $author['ppn'] = trim($ppn);
            }

        }*/
        return $tmp;
    }

    /**
     * Get an array of all the formats associated with the record.
     *
     * @return array
     * @access protected
     */
    public function getFormats()
    {
        $mediaTypesMap = MediaTypes::getMediaTypesMap();

        // format ist is detected by infos in Leader and kat 007
        $leader = $this->marcRecord->getLeader();
        $fields = $this->marcRecord->getFields("007", false);
        $tmp = $this->getFirstFieldValue('300', array('a'));

        $phys = [];

        if ($fields) {
            foreach ($fields as $field) {
                $data = $field->getData();
                if ($data[0] === 'c') {
                    // cd or dvd
                    if ($data[0] . $data[1] === 'co' && (strpos(strtoupper($tmp), 'DVD') === false)) {
                        $phys[] = 'cocd';
                    } else
                        $phys[] = $data[0] . $data[1];
                } else
                    $phys[] = $data[0];
            }
        } else {
            $phys[] = "xxx";
        }

        $art = $leader[6];
        $level = $leader[7];

        // now we have the three components art, level and phys.
        //For some formats this is not enough and we need additional infos

        // preliminary solution for detection of series
        //if (strpos($this->picaTit['002@']['0']['$0']['0'], "c") === 1 || strpos($this->picaTit['002@']['0']['$0']['0'], "d") === 1)
        $f002at00 = $this->picaRecord->getFirstMatchingField('002@')->getNthSubField(0, 0);

        if (strpos($f002at00, "c") === 1 || strpos($f002at00, "d" === 1)) {
            return ["series"];
        }

        // preliminary solution for articles
        if (strpos($f002at00, "o") === 1) {
            return ["article"];
        }

        // preliminary solution for retro
        if (strpos($f002at00, "r") === 0) {
            return ["retro"];
        }

        // return formats accourding to format arry in the beginning
        // of this method
        foreach ($phys as $p) {
            if (isset($mediaTypesMap[$art][$level][$p])) {
                return [$mediaTypesMap[$art][$level][$p]];
            }

        }

        // there is no format defined for the combination of art level and phys
        // for debugging
        return ["misc"];
    }

    /**
     * Titelanreicherung Vorerst über Pica Felder
     *
     * Format:
     * array(
     *        array(
     *               'title' => label_for_title
     *               'value' => link_name
     *               'link'  => link_URI
     *        ),
     *        ...
     * )
     *
     * @return array
     * @access public
     */
    public function getAllTitleLinks()
    {
        return $this->allTitleLinks;
    }

    /**
     * Get the Einheitssachtitel of the record.
     *
     * @return string
     * @access public
     */
    public function getPretitle()
    {

        // entweder 246 $a
        $fields = $this->marcRecord->getFields('246');
        $tmp = '';
        foreach ($fields as $field) {
            if (strcmp($field->getIndicator(2), '9') === 0) {
                $allSubfields = $field->getSubfields();
                foreach ($allSubfields as $currentSubfield) {
                    $code = trim($currentSubfield->getCode());
                    $data = trim($currentSubfield->getData());
                    if (strcmp($code, 'a') === 0) {
                        $tmp = $data;
                    }
                }
            }
        }
        if ($tmp === '') {
            // oder 240 $a <$g>
            $tmp1 = $this->getFieldArray('240', array('a'), false);
            if (count($tmp1) > 0) {
                $tmp = $tmp1[0];
            }

            $tmp2 = $this->getFieldArray('240', array('g'), false);
            if (count($tmp2) > 0) {
                $tmp = $tmp . ' <' . $tmp2[0] . '>';
            }
        }
        // Sortierzeichen weg
        if (strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }

        return $tmp;
    }

    /**
     * Get the Einheitssachtitel of the record.
     *
     * @return string
     * @access public
     */
    public function getPretitle2()
    {
        //240 $a <$g>
        $tmp = '';
        $tmp1 = $this->getFieldArray('730', array('a'), false);
        if (count($tmp1) > 0) {
            $tmp = $tmp1[0];
        }

        $tmp2 = $this->getFieldArray('730', array('g'), false);
        if (count($tmp2) > 0) {
            $tmp = $tmp . ' <' . $tmp2[0] . '>';
        }

        // Sortierzeichen weg
        if (strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }

        return $tmp;
    }

    /**
     * Get the RVK of the record.
     *
     * @return array
     * @access public
     */
    public function getShortSubtitle()
    {
        // 245 $b
        $tmp = '';
        $tmp1 = $this->getFieldArray('245', array('b'), false);
        if (count($tmp1) > 0) {
            $tmp = ": " . $tmp1[0];
        }

        // Sortierzeichen weg
        if (strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }
        return $tmp;
    }

    /**
     * Get the main corporate author (if any) for the record.
     *
     * @return string
     * @access public
     */
    public function getPartAuthors()
    {
        $authors = array();
        $tmp2 = array();
        $tmp3 = '';
        $fields = $this->marcRecord->getFields('700');

        // Extract all the requested subfields, if applicable.
        foreach ($fields as $key => $currentField) {
            $allSubfields = $currentField->getSubfields();
            $s0 = array();
            $a = '';
            $b = '';
            $c = '';
            $e = '';
            $s4 = array();

            foreach ($allSubfields as $currentSubfield) {
                $code = trim($currentSubfield->getCode());
                $data = trim($currentSubfield->getData());
                if (strcmp($code, '0') === 0) {
                    $s0[] = $data;
                }
                if (strcmp($code, 'a') === 0) {
                    $a = $data;
                }
                if (strcmp($code, 'b') === 0) {
                    $b = $data;
                }
                if (strcmp($code, 'c') === 0) {
                    $c = $data;
                }
                if (strcmp($code, 'e') === 0) {
                    $e = $data;
                }
                if (strcmp($code, '4') === 0) {
                    $s4[] = $data;
                }

            }
            if (!(in_array('aut', $s4) || in_array('hnr', $s4) || in_array('prf', $s4))) {
                $tmp3 = $a;

                if (strlen($b) > 0) {
                    $tmp3 = $tmp3 . " " . $b;
                }
                if (strlen($c) > 0) {
                    $tmp3 = $tmp3 . " <" . $c . ">";
                }
                if (strlen($e) > 0) {
                    $tmp2['e'] = " ($e)";
                }

                $tmp2['a'] = $tmp3;

                $authors[$key]['name'] = $tmp2;

                if (!empty($s0)) {
                    if (strpos($s0[0], '(DE-588)') !== false) {
                        $gnd = str_replace('(DE-588)', '', $s0[0]);
                        $authors[$key]['gnd'] = $gnd;
                    } else if (strpos($s0[0], '(DE-603)') !== false) {
                        $ppn = str_replace('(DE-603)', '', $s0[0]);
                        $authors[$key]['ppn'] = $ppn;
                    }
                }
            }
            $s0 = array();
            $a = '';
            $b = '';
            $c = '';
            $s4 = array();
            $tmp2 = array();
        }

        return $authors;
    }

    /**
     * Get Journal of an Article
     *
     * @return array
     * @access public
     */
    public function getJournal()
    {
        return $this->journal;
    }

    /**
     * Get the main corporation (if any) for the record.
     *
     * @return array
     * @access public
     */
    public function getCorporation()
    {
        $fieldnbrs = array('110', '111', '710', '711');
        $corporation = array();
        $tmp1 = '';
        $s0 = array();
        $x2 = '';


        foreach ($fieldnbrs as $fieldnr) {

            $fields = $this->marcRecord->getFields($fieldnr);

            // Extract all the requested subfields, if applicable.
            foreach ($fields as $key => $currentField) {
                $allSubfields = $currentField->getSubfields();

                foreach ($allSubfields as $currentSubfield) {
                    $code = trim($currentSubfield->getCode());
                    $data = trim($currentSubfield->getData());

                    // 110, 710
                    if (strcmp($fieldnr, '110') === 0 || strcmp($fieldnr, '710') === 0) {
                        if (strcmp($code, '0') === 0) {
                            $s0[] = $data;
                        }
                        if (strcmp($code, 'a') === 0) {
                            $tmp1 = $tmp1 . $data;
                        }
                        if (strcmp($code, 'b') === 0) {
                            $tmp1 = $tmp1 . ' / ' . $data;
                        }
                        if (strcmp($code, 'g') === 0) {
                            $tmp1 = $tmp1 . ' <' . $data . '>';
                        }
                        if (strcmp($code, 'n') === 0) {
                            $tmp1 = $tmp1 . ' <' . $data . '>';
                        }
                    }
                    // 111, 711
                    if (strcmp($fieldnr, '111') === 0 || strcmp($fieldnr, '711') === 0) {
                        if (strcmp($code, '0') === 0) {
                            $s0[] = $data;
                        }
                        if (strcmp($code, 'a') === 0) {
                            $tmp1 = $data;
                        }
                        if (strcmp($code, 'b') === 0) {
                            $x2 = $x2 . ', ' . $data;
                        }
                        if (strcmp($code, 'g') === 0) {
                            $x2 = $x2 . ', ' . $data;
                        }
                        if (strcmp($code, 'n') === 0) {
                            $x2 = $x2 . ', ' . $data;
                        }
                    }
                }
                if (strcmp($fieldnr, '111') === 0 || strcmp($fieldnr, '711') === 0) {
                    if (strlen($x2 > 1)) {
                        $tmp1 = $tmp1 . ' <' . substr($x2, 1) . '>';
                    }
                }

                $corporation[$key]['name'] = $tmp1;

                if (!empty($s0)) {
                    if (strpos($s0[0], '(DE-588)') !== false) {
                        $gnd = str_replace('(DE-588)', '', $s0[0]);
                        $corporation[$key]['gnd'] = $gnd;
                    } else if (strpos($s0[0], '(DE-603)') !== false) {
                        $ppn = str_replace('(DE-603)', '', $s0[0]);
                        $corporation[$key]['ppn'] = $ppn;
                    }
                }

                $tmp1 = '';
                $s0 = array();
                $x2 = '';
            }
        }
        return $corporation;
    }


    /**
     * Volumes
     *
     * @return array
     * @access public
     */
    public function getVolumes()
    {
        return $this->volumes;
    }

    /**
     *  --HeBIS
     *
     * array mit Werten zur Ausgabe der Kategorien der Sekundärausgaben
     *
     * @return array
     * @access public
     */
    public function getSecondaryCategories()
    {
        $leader = $this->marcRecord->getLeader();
        $sec = array();
        if (array_key_exists(0, $this->getFieldArray('530', array('a')))) {
            $sec["data"] = $this->getFieldArray('530', array('a'))[0];
        }

        if ($leader[6] . $leader[7] == "as") {
            $sec["Label"] = "Auch als";
            $fieldarr = $this->getFieldArray('533', array('7'));
            if (isset($fieldarr[0])) {
                $sec["data"] .= ", " . substr($fieldarr[0], 1, 4) . "-" . substr($fieldarr[0], 5, 4);
            }
            return $sec;
        }
        /*b, c, d, e, f, h, isbn */
        $subfields = ['b', 'c', 'd', 'e', 'f'];
        foreach ($subfields as $sf) {
            if (array_key_exists(0, $this->getFieldArray('533', [$sf]))) {
                $tmp = $this->getFieldArray('533', array($sf))[0];
                if (!empty($tmp)) {
                    $sec[$sf] = $this->getFieldArray('533', array($sf))[0];
                }
            }
        }
        if (!isset($sec['c'])) {
            $tmp = $this->getFieldArray('583', array('h'));
            if (!empty($tmp)) {
                $sec['c'] = $tmp;
            }
        }

        $fieldarr = $this->getFieldArray('020', array('a'));
        if (isset($fieldarr[0])) {
            $isbn = $fieldarr[0];
            $fieldarr = $this->getFieldArray('020', array('9'));
            if (preg_match('/sekundärausgabe/i', $isbn) && isset($fieldarr[0])) {
                $sec["isbn"] = $fieldarr[0];
            }
        }

        ksort($sec);
        foreach ($sec as $key => $val) {
            switch ($key) {
                case 'b':
                    $sec['data'] .= $val . ": ";
                    break;
                case 'c':
                    $sec['data'] .= $val . ", ";
                    break;
                case 'd':
                    $sec['data'] .= $val . ".";
                    break;
                case 'e':
                    $sec['data'] .= " - " . $val . ".";
                    break;
                case 'f':
                    $sec['data'] .= " - (" . $val . ")";
                    break;
                case 'isbn':
                    $sec['data'] .= ", " . $val;
                    break;
            }
        }
        return $sec;
    }

    /**
     * Get the full title of the record for hitlist.
     *
     * @return string
     * @access public
     */
    public function getTitle2()
    {
        // 245 $a
        $tmp = '';
        $tmp1 = $this->getFieldArray('245', array('a'), false);
        if (count($tmp1) > 0) {
            $tmp = $tmp1[0];
        }

        $tmp2 = $this->getFieldArray('245', array('h'), false);
        if (count($tmp2) > 0) {
            if (strlen($tmp) > 0) {
                $tmp = $tmp . ' ';
            }
            $tmp = $tmp . $tmp2[0];
        }

        // Sortierzeichen weg
        if (strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }

        return $tmp;
    }

    /**
     * Get the publish date of the current record. --HeBIS
     *
     * @return string
     * @access public
     */
    public function getPublishDate()
    {
        $fields = $this->marcRecord->getFields('260');
        $tmp = '';
        foreach ($fields as $field) {
            if (strcmp($field->getIndicator(1), '3') === 0) {
                $allSubfields = $field->getSubfields();
                foreach ($allSubfields as $currentSubfield) {
                    $code = trim($currentSubfield->getCode());
                    $data = trim($currentSubfield->getData());
                    if (strcmp($code, 'c') === 0) {
                        $tmp = $data;
                        break 2;
                    } else {
                        $tmp = "";
                    }
                }
            } else {
                $tmp = $this->getFirstFieldValue('260', array('c'));
            }
        }
        return substr(preg_replace('![^0-9]!', '', $tmp), 0, 4);
    }

    /**
     * Get bibliographical context of Journal
     *
     * @return array
     * @access public
     */
    public function getReviewed()
    {
        return $this->reviewed;
    }

    /**
     * Get bibliographical context of Journal
     *
     * @return array
     * @access public
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Get bibliographical context of Journal
     *
     * @return array
     * @access public
     */
    public function getJBibContext()
    {
        return $this->jbibcontext;
    }

    /**
     * Frühere / Spätere Titel
     *
     * @return array
     * @access public
     **/
    public function getJournalPrePost()
    {
        return $this->journalprepost;
    }

    /**
     * Andere Ausgaben
     *
     * @return array
     * @access public
     **/
    public function getOtherEditions()
    {
        return $this->otherEditions;
    }


    public function getSubtitleFormated()
    {
        // 245 $a_:_$b
        $tmp = array();
        $tmp1 = $this->getFieldArray('245', array('h'), false);
        if (count($tmp1) > 0) {
            $tmp = " " . $tmp1[0];
        }
        $tmp2 = $this->getFieldArray('245', array('b'), false);
        if (count($tmp2) > 0) {
            if (count($tmp) > 0) {
                $tmp = $tmp . ' : ' . $tmp2[0];
            } else {
                $tmp = ' : ' . $tmp2[0];
            }
        }

        $tmp3 = $this->getFieldArray('245', array('c'), false);
        if (count($tmp3) > 0) {
            if (count($tmp) > 0) {
                $tmp = $tmp . ' / ' . $tmp3[0];
            } else {
                $tmp = ' / ' . $tmp3[0];
            }
        }
        // Sortierzeichen weg
        if (is_string($tmp) && strpos($tmp, '@') !== false) {
            $occurrence = strpos($tmp, '@');
            $tmp = substr_replace($tmp, '', $occurrence, 1);
        }

        return $tmp;
    }


    /**
     * Does this record have an excerpt available?
     *
     * @return bool
     * @access public
     */
    public function hasExcerpt()
    {
        // If we have ISBN(s), we might have excerpts:
        $isbns = $this->getISBNs();

        // Do we have external excerpts? --HeBIS
        if (!empty($isbns)) {
            // No further checking because of http request --HeBIS
            /*$excerpts = $this->getExcerpts();
            if (!empty($excerpts)) {
                return true;
            }*/
            return true;
        }

        return false;
    }

    /**
     * Does this record have reviews available?
     *
     * @return bool
     * @access public
     */
    public function hasReviews()
    {
        // If we have ISBN(s), we might have reviews:
        $isbns = $this->getISBNs();

        // Do we have external reviews? --HeBIS
        if (!empty($isbns)) {
            // No further checking because of http request --HeBIS
            /*$reviews = $this->getReviews();
          if (!empty($reviews)) {
              return true;
          }*/
            return true;
        }

        return false;
    }

    /**
     * Does this record have a Description available? --HeBIS
     *
     * @return bool
     * @access public
     */
    public function hasDescription()
    {
        $annotation = $this->getAnnotation();
        return (!empty($this->jbibcontext) || !empty($annotation) ||
            !empty($this->review) || !empty($this->reviewed)) ? true : false;
    }

    /**
     * Does this record have a Table of Contents available? --HeBIS
     *
     * @return bool
     * @access public
     */
    public function hasExternalTOC()
    {
        // If we have ISBN(s), we might have table of contents:
        $isbns = $this->getISBNs();

        // Do we have external TOC? --HeBIS
        if (!empty($isbns)) {
            // No further checking because of http request --HeBIS
            /*$TOC = $this->getExternalTOC();
            if (!empty($TOC)) {
                return true;
            }*/
            return true;
        }

        return false;
    }

    /**
     * Does this record have a summary available? --HeBIS
     *
     * @return bool
     * @access public
     */
    public function hasSummary()
    {
        // If we have ISBN(s), we might have a summary:
        $isbns = $this->getISBNs();

        // Do we have external summaries? --HeBIS
        if (!empty($isbns)) {
            // No further checking because of http request --HeBIS
            /*$summaries = $this->getSummaries();
            if (!empty($summaries)) {
                return true;

            }*/
            return true;
        }

        return false;
    }

    /**
     * Return an associative array of copies of the item with specific information
     *
     * @return array
     * @access public
     */
    public function getCopies()
    {
        return $this->copies;
    }

    /**
     * Level1 Data
     *
     * @return array
     * @access public
     */
    public function getlvlOneData()
    {
        return $this->levelonedata;
    }

    /**
     * Get type of the part/section portion of the title.
     *
     * @return string
     * @access public
     */
    public function getTitleSectionType()
    {
        $leader = $this->marcRecord->getLeader();

        if ($leader[19] === 'c') {
            return 'Teil';
        } else {
            return 'Unterreihe';
        }
    }

    /**
     * Get contained media
     *
     * @return string
     * @access public
     */
    public function getContained()
    {
        $tmp = array();

        $tmp1 = $this->getFieldArray('249', array('a'), false);
        if (count($tmp1) > 0) {
            if (strpos($tmp1[0], '@') !== false) {
                $occurrence = strpos($tmp1[0], '@');
                $tmp1[0] = substr_replace($tmp1[0], '', $occurrence, 1);
            }
            $tmp[0] = $tmp1[0];
        }

        $tmp2 = $this->getFieldArray('249', array('b'), false);
        if (count($tmp2) > 0) {
            if (strpos($tmp2[0], '@') !== false) {
                $occurrence = strpos($tmp2[0], '@');
                $tmp2[0] = substr_replace($tmp2[0], '', $occurrence, 1);
            }
            $tmp[1] = $tmp2[0];
        }

        return $tmp;
    }

    /**
     * Get the interpreter (if any) for the record.
     *
     * @return array
     * @access public
     */
    public function getInterpreter()
    {
        $fieldnbrs = array('700', '710');
        $interpreter = array();
        $tmp700 = array();
        $tmp710 = array();
        $tmp1 = '';
        $tmp3 = '';
        $s0 = array();
        $s4 = array();


        foreach ($fieldnbrs as $fieldnr) {

            $fields = $this->marcRecord->getFields($fieldnr);

            // Extract all the requested subfields, if applicable.
            foreach ($fields as $key => $currentField) {
                $allSubfields = $currentField->getSubfields();

                foreach ($allSubfields as $currentSubfield) {
                    $code = trim($currentSubfield->getCode());
                    $data = trim($currentSubfield->getData());
                    $gnd = '';
                    $ppn = '';

                    // 700
                    if (strcmp($fieldnr, '700') === 0) {
                        if (strcmp($code, '0') === 0) {
                            $s0[] = $data;
                        }
                        if (strcmp($code, 'a') === 0) {
                            $tmp1 = $tmp1 . $data;
                        }
                        if (strcmp($code, 'b') === 0) {
                            $tmp1 = $tmp1 . ' ' . $data;
                        }
                        if (strcmp($code, 'c') === 0) {
                            $tmp1 = $tmp1 . ' <' . $data . '>';
                        }
                        if (strcmp($code, 'e') === 0) {
                            $tmp3 = $data;
                        }
                        if (strcmp($code, '4') === 0) {
                            $s4[] = $data;
                        }
                    }
                    // 710
                    if (strcmp($fieldnr, '710') === 0) {
                        if (strcmp($code, '0') === 0) {
                            $s0[] = $data;
                        }
                        if (strcmp($code, 'a') === 0) {
                            $tmp1 = $data;
                        }
                        if (strcmp($code, 'b') === 0) {
                            $tmp1 = $tmp1 . ' / ' . $data;
                        }
                        if (strcmp($code, 'g') === 0) {
                            $tmp1 = $tmp1 . ' <' . $data . '>';
                        }
                        if (strcmp($code, 'n') === 0) {
                            $tmp1 = $tmp1 . ' <' . $data . '>';
                        }
                        if (strcmp($code, '4') === 0) {
                            $s4[] = $data;
                        }
                    }
                }

                if (!empty($s0)) {
                    if (strpos($s0[0], '(DE-588)') !== false) {
                        $gnd = str_replace('(DE-588)', '', $s0[0]);
                    } else if (strpos($s0[0], '(DE-603)') !== false) {
                        $ppn = str_replace('(DE-603)', '', $s0[0]);
                    }
                }

                if (strcmp($fieldnr, '700') === 0 && in_array('prf', $s4)) {
                    $tmp700[$key]['name'] = $tmp1;
                    $tmp700[$key]['zusatz'] = $tmp3;
                    if (!empty($gnd)) {
                        $tmp700[$key]['gnd'] = $gnd;
                    } else {
                        $tmp700[$key]['ppn'] = $ppn;
                    }
                } else if (strcmp($fieldnr, '710') === 0 && in_array('mus', $s4)) {
                    $tmp710[$key]['name'] = $tmp1;
                    if (!empty($gnd)) {
                        $tmp710[$key]['gnd'] = $gnd;
                    } else {
                        $tmp710[$key]['ppn'] = $ppn;
                    }
                }

                $tmp1 = '';
                $s0 = array();
                $s4 = array();
            }
        }
        $interpreter[0] = $tmp700;
        $interpreter[1] = $tmp710;
        return $interpreter;
    }

    /**
     * Get an array of all secondary authors (complementing getPrimaryAuthor()).
     *
     * @return array
     * @access public
     */
    public function getRetroUrl()
    {
        return $this->retrourl;
    }

    /**
     * Assign necessary Smarty variables and return a template name to
     * load in order to display holdings extracted from the base record
     * (i.e. URLs in MARC 856 fields) and, if necessary, the ILS driver.
     * Returns null if no data is available.
     *
     * @param array $patron An array of patron data
     *
     * @return string Name of Smarty template file to display.
     * @access public
     */
    public function getHoldings($patron = false)
    {
        global $interface;
        global $configArray;
        $HebisSettings = getExtraConfigArray('HEBIS');

        $template = parent::getHoldings($patron);

        $interface->assign('holdingRetroURL', $this->getRetroUrl());
        if (in_array('series', $this->getFormats())) {
            $interface->assign('holdingSeries', true);
        }

        // Bandlisten --HeBIS
        if (isset($HebisSettings['Catalog']['bandlisten_enabled']) && $HebisSettings['Catalog']['bandlisten_enabled']) {
            $interface->assign('bandlistenEnabled', true);
        }

        // Popup statt neues Fenster für Bestellung/Vormerkung --HeBIS
        if (isset($HebisSettings['Catalog']['loanPopup']) && $HebisSettings['Catalog']['loanPopup']) {
            $interface->assign('loan_popup', true);
        }

        return $template;
    }

    /**
     * Field Subfield
     *
     * @return array
     * @access protected
     */
    protected function _getFieldSubfieldArray($field, $subfields)
    {
        // Default to subfield a if nothing is specified.
        if (!is_array($subfields)) {
            return array();
        }

        // Initialize return array
        $matches = array();

        // Try to look up the specified field, return empty array if it doesn't
        // exist.
        $fields = $this->marcRecord->getFields($field);
        if (!is_array($fields)) {
            return array();
        }

        // Extract all the requested subfields, if applicable.
        $tmp = array();
        foreach ($fields as $currentField) {
            $fsubfields = $currentField->getSubfields();
            foreach ($fsubfields as $fsubfield) {
                $code = trim($fsubfield->getCode());
                $data = trim($fsubfield->getData());
                if (in_array($code, $subfields)) {
                    $tmp[] = array($code, $data);
                }
            }
            if (count($tmp) > 0) {
                $matches[] = $tmp;
                $tmp = array();
            }
        }
        return $matches;
    }

    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @return array
     * @access protected
     */
    protected function getReportNumber()
    {
        // Try each MARC field one at a time:
        // 088
        return $this->_getFieldSubfieldArray('088', array('a'));
    }

    /**
     * Annotation
     *
     * @return array
     * @access protected
     */
    protected function getAnnotation()
    {

        // einfache Marc Felder
        $tmp = $this->_getFieldSubfieldArray('501', array('a'));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('546', array('a')));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('500', array('a')));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('515', array('a')));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('518', array('a')));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('538', array('a')));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('550', array('a')));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('555', array('a')));
        $tmp = array_merge($tmp, $this->_getFieldSubfieldArray('583', array('a', 'h')));

        // Marc Felder mit mehreren Bedingungen
        $fields = $this->marcRecord->getFields('246');
        $a = '';
        $i = '';
        foreach ($fields as $field) {
            if (strcmp($field->getIndicator(1), '1') === 0 ||
                strcmp($field->getIndicator(1), '0') === 0
            ) {
                $allSubfields = $field->getSubfields();
                foreach ($allSubfields as $currentSubfield) {
                    $code = trim($currentSubfield->getCode());
                    $data = trim($currentSubfield->getData());
                    if (strcmp($code, 'i') === 0) {
                        $i = $data;
                    }
                    if (strcmp($code, 'a') === 0) {
                        $a = $data;
                    }
                }
                If (strlen($i) > 0 && strlen($a) > 0) {
                    $tmp[] = array(array('a', $a));
                    $a = '';
                    $i = '';
                }
            }
        }
        $fields = $this->marcRecord->getFields('247');
        foreach ($fields as $field) {
            if (strcmp($field->getIndicator(2), '0') === 0) {
                $allSubfields = $field->getSubfields();
                foreach ($allSubfields as $currentSubfield) {
                    $code = trim($currentSubfield->getCode());
                    $data = trim($currentSubfield->getData());
                    if (strcmp($code, 'a') === 0) {
                        $tmp[] = array(array('a', $data));
                    }
                }
            }
        }

        // Pica Felder
        foreach ($this->picaTit as $key => $value) {
            if ($key === '046A' || $key === '046C' || $key === '046K') {
                if (isset($value['0']['$a']))
                    $tmp[] = array(array('a', $value['0']['$a']['0']));
            }
        }

        return $tmp;
    }

    /**
     * Get the Einheitssachtitel of the record.
     *
     * @return string
     * @access public
     */
    public function getRVK()
    {
        $rvk = array();
        $fields = $this->marcRecord->getFields('084');
        foreach ($fields as $currentField) {
            $allSubfields = $currentField->getSubfields();
            $a = '';
            $s2 = '';
            foreach ($allSubfields as $currentSubfield) {
                $code = trim($currentSubfield->getCode());
                $data = trim($currentSubfield->getData());

                if (strcmp($code, '2') === 0) {
                    $s2 = $data;
                }
                if (strcmp($code, 'a') === 0) {
                    $a = $data;
                }
                if (strcmp($s2, 'rvk') === 0 && strlen($a) > 0) {
                    // rvk with "."
                    $p = strpos($a, '.');
                    if ($p !== false) {
                        $a = substr($a, 0, $p);
                    }
                    // rvk with ";"
                    if (strpos($a, ';') !== false) {
                        $tmp2 = explode(';', $a);
                        foreach ($tmp2 as $a) {
                            $tmp[] = trim($a);
                        }
                    } else {
                        $tmp[] = $a;
                    }
                }

            }
            $a = '';
            $s2 = '';
        }

        // Get the description
        if (!empty($tmp)) {
            foreach ($tmp as $val) {
                if (!in_array($val, $rvk)) {
                    $rvk[] = $val;
                }
            }
        }

        return $rvk;
    }

    /**
     * Get the festschrift (if any) for the record.
     *
     * @return array
     * @access public
     */
    public function getFestschrift()
    {
        $festsch = array();
        $tmp2 = '';
        $fields = $this->marcRecord->getFields('700');

        // Extract all the requested subfields, if applicable.
        foreach ($fields as $key => $currentField) {
            $allSubfields = $currentField->getSubfields();
            $s0 = array();
            $a = '';
            $b = '';
            $c = '';
            $e = '';
            $s4 = array();

            foreach ($allSubfields as $currentSubfield) {
                $code = trim($currentSubfield->getCode());
                $data = trim($currentSubfield->getData());
                if (strcmp($code, '0') === 0) {
                    $s0[] = $data;
                }
                if (strcmp($code, 'a') === 0) {
                    $a = $data;
                }
                if (strcmp($code, 'b') === 0) {
                    $b = $data;
                }
                if (strcmp($code, 'c') === 0) {
                    $c = $data;
                }
                if (strcmp($code, 'e') === 0) {
                    $e = $data;
                }
                if (strcmp($code, '4') === 0) {
                    $s4[] = $data;
                }

            }
            if (in_array('hnr', $s4)) {
                $tmp2 = $a;

                if (strlen($b) > 0) {
                    $tmp2 = $tmp2 . " " . $b;
                }
                if (strlen($c) > 0) {
                    $tmp2 = $tmp2 . " <" . $c . ">";
                }
                $festsch[$key]['name'] = $tmp2;

                if (!empty($s0)) {
                    if (strpos($s0[0], '(DE-588)') !== false) {
                        $gnd = str_replace('(DE-588)', '', $s0[0]);
                        $festsch[$key]['gnd'] = $gnd;
                    } else if (strpos($s0[0], '(DE-603)') !== false) {
                        $ppn = str_replace('(DE-603)', '', $s0[0]);
                        $festsch[$key]['ppn'] = $ppn;
                    }
                }
            }
            $s0 = array();
            $a = '';
            $b = '';
            $c = '';
            $s4 = '';
            $tmp2 = '';
        }
        return $festsch;
    }


    /**
     * Get the Einheitssachtitel of the record.
     *
     * @return string
     * @access public
     */
    public function getThesis()
    {
        return $this->getFieldArray('502', array('a'));
    }


}
