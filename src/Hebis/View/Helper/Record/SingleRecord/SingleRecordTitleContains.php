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
use Hebis\Marc\Helper;

/**
 * Class SingleRecordTitleContains
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordTitleContains extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        $arr = [];
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $_249 = $marcRecord->getFields('249');

        /** @var \File_MARC_Data_Field $field */
        foreach ($_249 as $field) {
            $subFields_a = $field->getSubfields('a');
            $subFields_v = $field->getSubfields('v');
            $arr_av = [];

            // Wenn mehrere $a in 249, dann jeweils auf neuer Zeile anzeigen.
            // Die Kombi $a_/_$v als Pärchen zusammen anzeigen
            for ($i = 0; $i < count($subFields_a); ++$i) {
                /** @var \File_MARC_Subfield $subField_a */
                $subField_a = $subFields_a[$i];
                $av = htmlentities(Helper::utf8_encode($subField_a->getData()));
                if (array_key_exists($i, $subFields_v)) {
                    $av .= " / " . htmlentities(Helper::utf8_encode($subFields_v[$i]->getData()));
                }
                $arr_av[] = Helper::removeControlSigns($av);
            }
            $arr[] = implode("<br />", $arr_av);

            // 249 $b_/_$c
            $bc = Helper::getSubFieldDataOfGivenField($field, 'b');
            if (!empty($c = Helper::getSubFieldDataOfGivenField($field, 'c'))) {
                $bc .= " / $c";
            }
            if (!empty($bc)) {
                $arr[] = Helper::removeControlSigns($bc);
            }
        }

        //505 $a_$t_/_$r
        $_505 = $marcRecord->getFields('505');

        foreach ($_505 as $field) {
            $atr = Helper::getSubFieldDataOfGivenField($field, 'a');

            if (!empty($t = Helper::getSubFieldDataOfGivenField($field, 't'))) {
                $atr .= " $t";
            }

            if (!empty($r = Helper::getSubFieldDataOfGivenField($field, 'r'))) {
                $atr .= " / $r";
            }

            if (!empty($atr)) {
                $arr[] = trim(Helper::removeControlSigns($atr));
            }
        }

        return implode("<br />", $arr);
    }
}