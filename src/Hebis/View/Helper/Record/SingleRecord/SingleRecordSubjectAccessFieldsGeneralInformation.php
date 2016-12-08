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

namespace Hebis\View\Helper\Record\SingleRecord;
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;


/**
 * Class SingleRecordSubjectAccessFieldsGeneralInformation
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordSubjectAccessFieldsGeneralInformation extends AbstractRecordViewHelper
{
    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record, $htmlOutout = false)
    {
        $arr = [];
        $line600 = ($this->add600($record));
        if (!empty($line600)) {
            $arr[] = $line600;
        }
        $line610 = ($this->add610($record));
        if (!empty($line610)) {
            $arr[] = $line610;
        }
        $line611 = ($this->add611($record));
        if (!empty($line611)) {
            $arr[] = $line611;
        }
        $line630 = ($this->add630($record));
        if (!empty($line630)) {
            $arr[] = $line630;
        }
        $line650 = ($this->add650($record));
        if (!empty($line650)) {
            $arr[] = $line650;
        }
        $line651 = ($this->add651($record));
        if (!empty($line651)) {
            $arr[] = $line651;
        }
        $line655 = ($this->add655($record));
        if (!empty($line655)) {
            $arr[] = $line655;
        }
        $line648 = ($this->add648($record));
        if (!empty($line648)) {
            $arr[] = $line648;
        }

        if (!$htmlOutout) {
            return implode("<br />", $arr);
        }
        else {
            return $arr;
        }

    }

    /**
     * @param SolrMarc $record
     * @return string
     * @internal param $line
     */
    private function add600(SolrMarc $record)
    {
        $arr = [];
        /** @var \File_MARC_Data_Field $field */
        foreach ($record->getMarcRecord()->getFields(600) as $field) {
            $arr_ = [];

            /** @var \File_MARC_Subfield $sf */
            foreach ($field->getSubfields() as $sf) {
                if (!in_array($sf->getCode(), ['a', 'b', 'c', 't', 'x'])) {
                    continue;
                }
                $line = "";
                switch ($sf->getCode()) {
                    case 'a':
                        $line .= !empty($sf) ? htmlentities($sf->getData()) : "";
                        break;
                    case 'b':
                        $line .= !empty($sf) ? htmlentities($sf->getData()) : "";
                        break;
                    case 'c':
                        $line .= !empty($sf) ? "&lt;" . htmlentities($sf->getData()) . "&gt;" : "";
                        break;
                    case 't':
                        $line .= !empty($sf) ? "/ " . htmlentities($sf->getData()) : "";
                        break;
                    case 'x':
                        $line .= !empty($sf) ? ", " . htmlentities($sf->getData()) : "";
                        break;
                }
                $arr_[] = $line;
            }
            $arr[] = implode(" ", $arr_);
        }

        return implode("<br />", $arr);
    }



    private function add610($record)
    {
        $arr = [];
        /** @var \File_MARC_Data_Field $field */
        foreach ($record->getMarcRecord()->getFields(610) as $field) {
            /** @var \File_MARC_Subfield $sf */
            $line = "";
            foreach ($field->getSubfields() as $sf) {
                if (!in_array($sf->getCode(), ['a', 'b', 'g', 't', 'f', 'x'])) {
                    continue;
                }

                switch ($sf->getCode()) {
                    case 'a':
                        $line .= !empty($sf) ? htmlentities($sf->getData()) : "";
                        break;
                    case 'b':
                        $line .= !empty($sf) ? " / " . htmlentities($sf->getData()) : "";
                        break;
                    case 'g':
                        $line .= !empty($sf) ? " &lt;" . htmlentities($sf->getData()) . "&gt;" : "";
                        break;
                    case 't':
                        $line .= !empty($sf) ? " / " . htmlentities($sf->getData()) : "";
                        break;
                    case 'f':
                        $line .= !empty($sf) ? " (" . htmlentities($sf->getData()).")" : "";
                        break;
                    case 'x':
                        $line .= !empty($sf) ? ", " . htmlentities($sf->getData()) : "";
                        break;
                }
            }
            $arr[] = $line;
        }
        return implode("<br />", $arr);
    }

    private function add611(SolrMarc $record)
    {
        $arr = [];
        /** @var \File_MARC_Data_Field $field */
        foreach ($record->getMarcRecord()->getFields(611) as $field) {
            $sf_ = $field->getSubfields();
            //['a', 'c', 'd', 'e', 'f', 'g', 'n', 't', 'x'];
            $line = "";
            /** @var \File_MARC_Subfield $sf */
            foreach ($sf_ as $sf) {
                $key = $sf->getCode();
                switch ($key) {
                    case 'a':
                        $line .= !empty($sf) ? $sf->getData() : "";
                        break;
                    case 'c':
                    case 'd':
                    case 'f':
                    case 'n':
                    case 'x':
                        $line .= !empty($sf) ? ", " . $sf->getData() : "";
                        break;
                    case 'e':
                        $line .= !empty($sf) ? " / " . $sf->getData() : "";
                        break;
                    case 'g':
                        $line .= !empty($sf) ? " &lt;" . $sf->getData() . "&gt;" : "";
                        break;
                    case 't':
                        $line .= !empty($sf) ? " / " . $sf->getData() : "";
                }

            }
            $arr[] = $line;
        }
        return implode("<br />", $arr);
    }

    private function add630($record)
    {
        $arr = [];
        foreach ($record->getMarcRecord()->getFields(630) as $field) {
            $sf_ = $this->getSubFieldsDataOfField($field, ['a', 'c', 'd', 'e', 'f', 'g', 't', 'x']);
            foreach ($sf_ as $sf) {
                $line = "";
                $line .= !empty($sf['a']) ? $sf['a'] : "";
                $line .= !empty($sf['c']) ? ", " . $sf['c'] : "";
                $line .= !empty($sf['d']) ? ", " . $sf['d'] : "";

                $line .= !empty($sf['e']) ? " / " . $sf['e'] : "";
                $line .= !empty($sf['f']) ? " &lt;" . $sf['f'] . "&gt;" : "";
                $line .= !empty($sf['g']) ? ", " . $sf['g'] : "";

                $line .= !empty($sf['t']) ? " / " . $sf['t'] : "";
                $line .= !empty($sf['x']) ? ", " . $sf['x'] : "";
                $arr[] = $line;
            }
        }
        return implode("<br />", $arr);
    }

    private function add650($record)
    {
        $arr = [];
        foreach ($record->getMarcRecord()->getFields(650) as $field) {
            $sf_ = $this->getSubFieldsDataOfField($field, ['a', 'c', 'x', 'g']);
            $line = "";
            foreach ($sf_ as $sf) {
                $line .= !empty($sf['a']) ? $sf['a'] : "";
                $line .= !empty($sf['c']) ? " &lt;" . $sf['c'] . "&gt;" : "";
                $line .= !empty($sf['x']) ? ", " . $sf['x'] : "";
                $line .= !empty($sf['g']) ? " &lt;" . str_replace("g:", "", $sf['g']) . "&gt;" : "";
                $arr[] = $line;
            }
            
        }
        return implode("<br />", $arr);


    }

    private function add651($record)
    {
        $arr = [];
        foreach ($record->getMarcRecord()->getFields(651) as $field) {

            $sf_ = $this->getSubFieldsDataOfField($field, ['a', 'g', 'x', 'z']);
            foreach ($sf_ as $sf) {
                $line = "";
                $line .= !empty($sf['a']) ? $sf['a'] : "";
                $line .= !empty($sf['g']) ? ", " . $sf['g'] : "";
                $line .= !empty($sf['x']) ? ", " . $sf['x'] : "";
                $line .= !empty($sf['z']) ? "," . $sf['z'] : "";
                $arr[] = $line;
            }
        }
        return implode("<br />", $arr);
    }

    private function add655($record)
    {
        $arr = [];
        foreach ($record->getMarcRecord()->getFields(655) as $field) {
            $sf_ = $this->getSubFieldsDataOfField($field, ['a', 'x', 'y', 'z']);
            foreach ($sf_ as $sf) {
                $arr[] = implode(", ", $sf);
            }
        }
        return implode("<br />", $arr);
    }

    private function add648($record)
    {
        return $this->getSubFieldDataOfField($record, '648', 'a');
    }
}