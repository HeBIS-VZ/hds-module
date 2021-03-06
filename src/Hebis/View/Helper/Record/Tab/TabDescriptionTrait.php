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

namespace Hebis\View\Helper\Record\Tab;

use Hebis\Marc\Helper;
use Hebis\RecordDriver\SolrMarc;

trait TabDescriptionTrait
{

    public function getNotes(SolrMarc $record)
    {
        $arr = [];

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $fields246 = $marcRecord->getFields(246);
        /** @var \File_MARC_Data_Field $field */
        foreach ($fields246 as $field) {
            $i = Helper::getSubField($field, "i");
            if (
                ($field->getIndicator(1) == 0 && !empty($i)) ||
                ($field->getIndicator(1) == 1 && !empty($i))
            ) {
                if ($field->getIndicator(1) == "0" || $field->getIndicator(1) == "1") {
                    $arr[] = "$i: " . Helper::getSubField($field, "a");
                }
            }
        }

        $fields247 = $marcRecord->getFields(247);
        /** @var \File_MARC_Data_Field $field */
        foreach ($fields247 as $field) {

            if ($field->getIndicator(2) == "0") {
                $a = Helper::getSubField($field, "a");
                $f = Helper::getSubField($field, "f");
                $res = !empty($a) ? "$a" : "";
                $res .= !empty($f) ? ", $f" : "";
                $arr[] = $res;
            }

        }

        foreach ([500, 501, 504, 511, 515, 518, 538, 546, 550, 555] as $fieldNum) {
            $fields = $marcRecord->getFields($fieldNum);
            /** @var \File_MARC_Data_Field $field */
            foreach ($fields as $field) {
                $a = Helper::getSubField($field, "a");
                if (!empty($a)) {
                    $arr[] = $a;
                }
            }
        }

        foreach ($marcRecord->getFields(583) as $field) {
            $a = Helper::getSubField($field, "a");
            $h = Helper::getSubField($field, "h");
            $res = !empty($a) ? "$a" : "";
            $res .= !empty($h) ? " : $h" : "";
            if (!empty($res)) {
                $arr[] = $res;
            }
        }

        return implode("<br />\n", $arr);
    }

    public function getReferenceNote(SolrMarc $record)
    {
        $arr = [];

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $fields510 = $marcRecord->getFields(510);

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields510 as $field) {
            $a = $field->getSubfield("a");
            if (!empty($a)) {
                $arr[] = $a->getData();
            }
        }

        return implode("<br />", $arr);
    }
}