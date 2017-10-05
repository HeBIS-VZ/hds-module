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
        $contentType = new ContentType();
        $this->isMultipartItem = $contentType->getContentType($driver) === "hierarchy";
        return $this;
    }

    /**
     * @return bool
     */
    public function isMultipartItem()
    {
        return $this->isMultipartItem;
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
            $np = $this->getSubFieldsDataArrayOfField($field, ['n', 'p']);
            if (!empty($np)) {
                $str .= "; " . $np['n'];
                if (array_key_exists("p", $np)) {
                    $str .= ". " . $np['p'];
                }
            }


            $ppn = $this->getPPNFrom773();
            if (!empty($ppn)) {
                $uri = new Uri($this->getView()->url('recordfinder') . "HEB" . $ppn);
                $str = '<a href="' . $uri->toString() . '">' . $str . '</a>';
            }
            if (!empty($link = $this->renderShowAllVolumesLink())) {
                $str .= "<br />" . '<span class="hds-icon-list-bullet">' . $link . '</span>';
            }
            $arr[] = $str;
        }

        /*
        800 $a_$b,_$c:_$t_:_$n,_$p_;_$v
        810 $a._$b_($g)_($n):_$t_:_$n,_$p_;_$v
        811 $a_($g)_($n_:_$c_:_$d):_$t_:_$n,_$p_;_$v
        830 $a_:_$n,_$p_;_$v
        + Zusatzregeln (s. Anmerkungen) */

        $fields = $marcRecord->getFields(800);
        foreach ($fields as $field) {
            $arr[] = $this->generate800($field) . $this->renderShowAllVolumesLink8xx($field);
        }

        $fields = $marcRecord->getFields(810);
        foreach ($fields as $field) {
            $arr[] = $this->generate810($field) . $this->renderShowAllVolumesLink8xx($field);
        }

        $fields = $marcRecord->getFields(811);
        foreach ($fields as $field) {
            $arr[] = $this->generate811($field) . $this->renderShowAllVolumesLink8xx($field);
        }

        $fields = $marcRecord->getFields(830);
        foreach ($fields as $field) {
            $arr[] = $this->generate830($field) . $this->renderShowAllVolumesLink8xx($field);
        }


        /*
        Wenn Leader Pos. 19 = # oder a:
        490 $a,_$x_;_$v
        "ISSN_" vor $x ergänzen */
        $fields = $marcRecord->getFields(490);

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            $ind1 = $field->getIndicator(1);
            if ($ind1 == "0") {
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

        $c_t = $this->getSubFieldsDataArrayOfField($field, ['c', 't']);

        if (array_key_exists('c', $c_t)) {
            $ret .= ", " . $c_t['c'];
        }

        if (array_key_exists('t', $c_t)) {
            $ret .= ": " . $c_t['t'];
        }

        $ret = $this->createLink($field, $ret);

        $n = Helper::getSubFieldDataOfGivenField($field, 'n');
        if ($n !== false && strpos($n, "[...]") === false) {
            $n = " : $n";
        } else {
            $n = "";
        }
        if (!empty($n)) {
            $ret .= $n;
        }
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
            }
        }

        $ret = $this->createLink($field, $ret);

        foreach ($field->getSubfields() as $code => $subfield) {
            switch ($code) {
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

                    } else {
                        if ($code == "n" && $tCalled) {
                            $ret .= " : " . $subfield->getData();
                        }
                    }
                    break;
                case 't':
                    $tCalled = true;
                    $ret .= ": " . $subfield->getData();
                    break;
            }
        }

        $ret = $this->createLink($field, $ret);

        foreach ($field->getSubfields() as $code => $subfield) {
            switch ($code) {
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
                case 't':
                    $ret .= ": " . htmlentities($subfield->getData());
                    break;
            }
        }

        $ret = $this->createLink($field, $ret);

        foreach ($field->getSubfields() as $code => $subfield) {
            switch ($code) {
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
        return $this->getPPNFrom($_773);
    }

    private function getPPNFrom($field)
    {
        if (!empty($field)) {
            $w = $field->getSubfield("w");
            if (!empty($w) && !empty($w->getData())) {
                $ppn = substr($w->getData(), 8);
            }
            return $ppn;
        }
        return "";
    }

    /**
     * @param string $field
     * @param string $ret
     * @return string
     */
    private function createLink($field, $ret)
    {
        $ppn = $this->getPPNFrom($field);
        $_7 = $field->getSubfield(7);
        if (!empty($ppn) && !empty($_7) && substr($_7->getData(), 1, 1) === "m") {
            $uri = new Uri($this->getView()->url('recordfinder') . "HEB" . $ppn);
            $ret = '<a href="' . $uri->toString() . '">' . $ret . '</a>';
        }
        return $ret;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    private function renderShowAllVolumesLink8xx($field)
    {
        $_7 = $field->getSubfield(7);
        if (!empty($_7) && substr($_7->getData(), 1, 1) === "m") {
            $ppn = $this->getPPNFrom($field);

            $linkText = $this->getView()->transEsc('show_all_volumes');
            $searchParams = $this->getShowAllVolumesLinkParams($ppn);

            if (!empty($link = $this->generateSearchLink($linkText, $searchParams))) {
                $link = "<br />" . '<span class="hds-icon-list-bullet">' . $link . '</span>';
            }
            return $link;
        }
        return "";
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
        $searchParams = $this->getShowAllVolumesLinkParams($ppn);

        return $this->generateSearchLink($linkText, $searchParams);

    }
    /**
     * @param $ppn
     * @return array
     */
    private function getShowAllVolumesLinkParams($ppn)
    {
       return [
            "sort" => "pub_date_max desc",
            "type0[]" => "part_of",
            "lookfor0[]" => $ppn,
            "join" => "AND"
        ];
    }
}
