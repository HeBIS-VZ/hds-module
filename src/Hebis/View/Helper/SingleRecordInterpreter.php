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

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordInterpreter extends SingleRecordAddedEntryPersonalName
{
    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $arr = [];

        $fields = $marcRecord->getFields('700');

        $fields_ = array_filter($fields, function(\File_MARC_Data_Field $field) {
            $subField = $field->getSubfield('4');
            return !empty($subField) && in_array($subField->getData(), ['prf', 'mus']);
        });



        /** @var \File_MARC_Data_Field $field */
        foreach ($fields_ as $field) {
            $_700 = "";
            list($a, $b) = $this->extractDataFromSubFieldAB($field);

            $c = $this->getSubFieldDataOfGivenField($field, 'c');
            $e = $this->getSubFieldDataOfGivenField($field, 'e');
            $_700 .= $a ? $a : "";
            $_700 .= $b ? " $b" : "";
            $_700 .= $c ? " <$c>" : "";
            $_700 .= $e ? " ($e)" : "";

            $arr[] = $this->authorSearchLink($_700);
        }


        $ret = implode("; ", $arr);

        $arr = []; //reset array

        $fields = $marcRecord->getFields('710');

        $fields_ = array_filter($fields, function(\File_MARC_Data_Field $field) {
            $subField = $field->getSubfield('4');
            return !empty($subField) && in_array($subField->getData(), ['prf', 'mus']);
        });

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields_ as $field) {
            $_710 = "";
            list($a, $b) = $this->extractDataFromSubFieldAB($field);
            $g = $this->getSubFieldDataOfGivenField($field, 'g');
            $n = $this->getSubFieldDataOfGivenField($field, 'n');
            $_710 .= $a ? $a : "";
            $_710 .= $b ? " / $b" : "";
            $_710 .= $g ? " <$g>" : "";
            $_710 .= $n ? " <$n>" : "";

            $arr[] = $this->authorSearchLink($_710);
        }
        
        $ret .= "<br />" . implode("; ", $arr);

        return $ret;
    }

    /**
     * @param $field
     * @return array
     */
    private function extractDataFromSubFieldAB($field)
    {
        $a = $this->getSubFieldDataOfGivenField($field, 'a');
        $b = $this->getSubFieldDataOfGivenField($field, 'b');
        return array($a, $b);
    }


}