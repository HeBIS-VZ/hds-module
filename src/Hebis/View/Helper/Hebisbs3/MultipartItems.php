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

namespace Hebis\View\Helper\Hebisbs3;

use Hebis\Exception\HebisException;
use Hebis\Marc\Helper;
use Hebis\RecordDriver\ContentType;
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use Zend\ServiceManager\ServiceManager;
use Zend\Uri\Uri;

/**
 * Class MultipartItems
 * @package Hebis\View\Helper\Hebisbs3
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class MultipartItems extends AbstractRecordViewHelper
{

    private $sm;

    /**
     * @var bool
     */
    private $isMultipartItem;

    /**
     * @var bool
     */
    private $isPartOfMultipartItem;

    /**
     * @var SolrMarc
     */
    private $driver;

    /**
     * MultipartItems constructor.
     * @param ServiceManager $sm
     */
    public function __construct(ServiceManager $sm = null)
    {
        $this->sm = $sm;
    }

    /**
     * Store a record driver object and return this object so that the appropriate
     * template can be rendered.
     *
     * @param \Hebis\RecordDriver\SolrMarc $driver Record driver object.
     *
     * @return MultipartItems
     */
    public function __invoke($driver)
    {
        $this->driver = $driver;
        $this->isMultipartItem = ContentType::getContentType($driver) === "hierarchy";
        return $this;
    }

    /**
     * @return bool
     */
    public function isMultipartItem()
    {
        return $this->isMultipartItem;
    }

    public function renderShowAllVolumesLink()
    {
        if ($this->isMultipartItem) {
            $ppn = substr($this->driver->getPPN(), 3);
        } else {
            $ppn = $this->getPPNFrom773();
            if (empty($ppn)) {
                return "";
            }
        }

        if (empty($ppn)) {
            throw new HebisException("Invalid state. No PPN present to generate link");
        }

        $linkText = $this->getView()->transEsc('show_all_volumes');
        $searchParams = [
            "sort" => "relevance",
            "type0[]" => "part_of",
            "lookfor0[]" => $ppn,
            "join" => "AND"];

        return $this->generateSearchLink($linkText, $searchParams);

    }


    public function isPartOfMultipartItem()
    {
        return $this->isMultipartItem;
    }

    public function renderParentalItem()
    {

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $this->driver->getMarcRecord();

        /* Wenn  Leader Pos. 19 = c:
           245 $a_:_$b_/_$c
           + Zusatzregeln (s. Anmerkungen) */
        $arr = [];
        $leader = $marcRecord->getLeader();
        if (substr($leader, 19, 1) === "c") {
            $field = $marcRecord->getField(245);

            $str = "";
            $ab = $this->getSubFieldsDataArrayOfField($field, ['a', 'b']);
            $glue = " : ";
            if (array_key_exists('b', $ab) && substr(trim($ab['b']), 0, 1) === "=") {
                //$ab['b'] = substr(trim($ab['b']),2);
                $glue = " ";
            }
            $str .= implode($glue, $ab);

            if (!empty($c = Helper::getSubFieldDataOfGivenField($field, 'c'))) {
                $str .= " / $c";
            }
            $ppn = $this->getPPNFrom773();
            if (!empty($ppn)) {
                $uri = new Uri($this->getView()->url('recordfinder') . "HEB" . $ppn);
                $arr[] = '<a href="'.$uri->toString().'">'.$str.'</a>';
            } else {
                $arr[] = $str;
            }
        }

        /* Wenn Leader Pos. 19 = b oder c:
        800 $a_$b,_$c:_$t_:_$n,_$p_;_$v
        810 $a._$b_($g)_($n):_$t_:_$n,_$p_;_$v
        811 $a_($g)_($n_:_$c_:_$d):_$t_:_$n,_$p_;_$v
        830 $a_:_$n,_$p_;_$v
        + Zusatzregeln (s. Anmerkungen) */


        if (substr($leader, 19, 1) === "c" || substr($leader, 19, 1) === "b") {
            $fields = $marcRecord->getFields(800);
            foreach ($fields as $field) {
                $arr[] = $this->generate800($field);
            }

            $fields = $marcRecord->getFields(810);
            foreach ($fields as $field) {
                $arr[] = $this->generate810($field);
            }

            $fields = $marcRecord->getFields(811);
            foreach ($fields as $field) {
                $arr[] = $this->generate811($field);
            }

            $fields = $marcRecord->getFields(830);
            foreach ($fields as $field) {
                $arr[] = $this->generate830($field);
            }
        }

        /*
        Wenn Leader Pos. 19 = # oder a:
        490 $a,_$x_;_$v
        "ISSN_" vor $x ergänzen */
        if (substr($leader, 19, 1) === " " || substr($leader, 19, 1) === "a") {
            $fields = $marcRecord->getFields(490);
            foreach ($fields as $field) {
                $ax = $this->getSubFieldsDataArrayOfField($field, ['a', 'x']);
                if (array_key_exists('x', $ax)) {
                    $ax['x'] = "ISSN " . $ax['x'];
                }
                $str = implode(", ", $ax);

                if (!empty($v = Helper::getSubFieldDataOfGivenField($field, 'v'))) {
                    $str .= " ; $v";
                }
                $arr[] = trim($str);
            }
        }

        return implode("<br />", $arr);
    }

    private function generate800($field)
    {
        $ret = "";
        //800 $a_$b,_$c:_$t_:_$n,_$p_;_$v
        $ret .= implode(" ", $this->getSubFieldsDataArrayOfField($field, ['a', 'b']));

        $c_t = implode(": ", $this->getSubFieldsDataArrayOfField($field, ['c', 't']));
        $ret .= (!empty($c_t)) ? ", $c_t" : "";

        $n = Helper::getSubFieldDataOfGivenField($field, 'n');
        if (strpos($n, "[...]") === false) {
            $n = " : $n";
        } else {
            $n = "";
        }

        $ret .= $n;
        $ret .= !empty($p = Helper::getSubFieldDataOfGivenField($field, 'p')) ? ", $p" : "";


        $v = Helper::getSubFieldDataOfGivenField($field, 'v');
        $ret .= (!empty($v)) ? " ; $v" : "";

        return $ret;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    private function generate810($field)
    {
        $tCalled = false;
        $ret = "";
        foreach ($field->getSubfields() as $code => $subfield) {
            switch ($code) {
                case 'a':
                    $ret .= $subfield->getData();
                    break;
                case 'b':
                    $ret .= ". " . $subfield->getData();
                    break;
                case 'g':
                    $ret .= " (" . $subfield->getData() . ")";
                    break;
                case 'n':
                    if (!$tCalled) { //vor dem t
                        $ret .= " (" . $subfield->getData() . ")";
                    } else {
                        if (strpos($subfield->getData(), "[...]") === false) {
                            $ret .= " : " . $subfield->getData();
                        }
                    }
                    break;
                case 't':
                    $tCalled = true;
                    $ret .= ": " . $subfield->getData();
                    break;
                case 'p':
                    $ret .= ", " . $subfield->getData();
                    break;
                case 'v':
                    $ret .= " ; " . $subfield->getData();
                    break;
            }
        }

        return $ret;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    private function generate811($field)
    {
        $ret = "";

        $n_ = $field->getSubfields('n');
        $c = $field->getSubfields('c');
        $d = $field->getSubfields('d');
        $tCalled = false;
        $ncdCalled = false;
        /**
         * @var string $code
         * @var \File_MARC_Subfield $subfield
         */
        foreach ($field->getSubfields() as $code => $subfield) {
            switch ($code) {
                case 'a':
                    $ret .= $subfield->getData();
                    break;
                case 'g':
                    $ret .= " (" . $subfield->getData() . ")";
                    break;
                case 'n':
                case 'c':
                case 'd':
                    if ($ncdCalled) {
                        continue;
                    }
                    $ncd = [];
                    if (($code == "n" && !$tCalled) || ($code == "c" || $code == "d")) { //vor dem t
                        if (!$tCalled) {
                            $ncd[] = $n_[0]->getData();
                        }
                        if (!empty($c)) {
                            $ncd[] = $c[0]->getData();
                        }
                        if (!empty($d)) {
                            $ncd[] = $d[0]->getData();
                        }
                        if (!empty($ncd)) {
                            $ret .= " (" . implode(" : ", $ncd) . ")";
                        }
                        $ncdCalled = true;

                    } else if ($code == "n" && $tCalled) {
                        $ret .= " : " . $subfield->getData();
                    }
                    break;
                case 't':
                    $tCalled = true;
                    $ret .= ": " . $subfield->getData();
                    break;
                case 'p':
                    $ret .= ", " . $subfield->getData();
                    break;
                case 'v':
                    $ret .= " ; " . $subfield->getData();
                    break;
            }
        }

        return $ret;

    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    private function generate830($field)
    {
        $ret = "";
        foreach ($field->getSubfields() as $code => $subfield) {
            switch ($code) {
                case 'a':
                    $ret .= htmlentities($subfield->getData());
                    break;
                case 'n':
                    if (strpos($subfield->getData(), "[...]") === false) {
                        $ret .= " : " . htmlentities($subfield->getData());
                    }
                    break;
                case 'p':
                    $ret .= ", " . htmlentities($subfield->getData());
                    break;
                case 'v':
                    $ret .= " ; " . htmlentities($subfield->getData());
            }
        }
        return $ret;
    }

    protected function getSubFieldsDataArrayOfField(\File_MARC_Data_Field $field, $subFieldSubFieldCodes = [])
    {
        $arr = [];

        foreach ($subFieldSubFieldCodes as $subFieldCode) {
            $ar = $this->getSubFieldDataArrayOfGivenField($field, $subFieldCode);
            if (empty($ar)) {
                continue;
            }
            $arr[$subFieldCode] = $ar[0];
        }

        return $arr;
    }

    /**
     * @return bool|string
     */
    private function getPPNFrom773()
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $this->driver->getMarcRecord();

        /** @var \File_MARC_Data_Field $_773 */
        $_773 = $marcRecord->getField(773);
        if (!empty($_773)) {
            $w = $_773->getSubfield("w");
            if (!empty($w) && !empty($w->getData())) {
                $ppn = substr($w->getData(), 8);
            }
            return $ppn;
        }
        return "";
    }

}