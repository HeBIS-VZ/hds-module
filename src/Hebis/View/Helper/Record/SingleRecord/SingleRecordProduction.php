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
use Hebis\View\Helper\Record\MarcSubfieldManipulationTrait;


/**
 * Class SingleRecordProduction
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordProduction extends AbstractRecordViewHelper
{
    use MarcSubfieldManipulationTrait;

    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        $id = $record->getUniqueID();

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $fields = $marcRecord->getFields('264');

        $fields = array_filter($fields, function($field) {
            $ind2 = $field->getIndicator(2);
            $ind1 = $field->getIndicator(1);
            return $ind2 === "3" && in_array($ind1, ["3", "2", " ", ""]);
        });

        usort($fields, function (\File_MARC_Data_Field $fieldA, \File_MARC_Data_Field $fieldB) {
            return (-1) * strcmp($fieldA->getIndicator(1), $fieldB->getIndicator(1));
        });

        return $this->generateContent($fields);
    }

    /**
     * @param $fields
     * @return string
     */
    protected function generateContent($fields)
    {
        $arr = [];
        foreach ($fields as $field) {
            $a = implode("; ", $this->getSubFieldsStringArrayOfGivenField($field, ['a']));
            $b = implode("; ", $this->getSubFieldsStringArrayOfGivenField($field, ['b']));
            $c = implode("; ", $this->getSubFieldsStringArrayOfGivenField($field, ['c']));

            $str = $a;
            $str .= (!empty($b) ? " : $b" : "");
            $str .= (!empty($c) ? ", $c" : "");
            $arr[] = $str;
        }
        return implode("<br />", $arr);
    }

}