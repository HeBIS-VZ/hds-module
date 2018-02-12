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

namespace Hebis\Marc;

use Hebis\RecordDriver\SolrMarc;

class Helper
{

    const DEFAULT_CHARSET = "UTF-8";

    /**
     * returns the data of the subField if field and subField exists, otherwise false
     *
     * @param SolrMarc $record
     * @param $fieldCode
     * @param $subFieldCode
     * @return bool|string
     */
    public static function getSubFieldDataOfField(\File_MARC_Record $record, $fieldCode, $subFieldCode)
    {

        /** @var \File_MARC_Data_Field $field */
        $field = $record->getField($fieldCode);

        return self::getSubFieldDataOfGivenField($field, $subFieldCode);
    }

    /**
     * if field of type \File_MARC_Data_Field and it has a subField with $subFieldCode this function returns the data
     * string of the subField, otherwise false.
     *
     * @param $field
     * @param $subFieldCode
     * @return bool|string
     */
    public static function getSubFieldDataOfGivenField($field, $subFieldCode)
    {
        if ($field && $field instanceof \File_MARC_Data_Field) {
            $subField = $field->getSubfield($subFieldCode);
            return !empty($subField) ? htmlentities(Helper::utf8_encode($subField->getData())) : false;
        }

        return false;
    }

    public static function removeControlSigns($str)
    {
        $len = strlen($str);
        if (strpos($str, '@') === 0) {
            $str = substr($str, 1, $len - 1);
        }

        $str = str_replace(" @", " ", $str);
        $ret = str_replace(["", ""], "", $str);
        return trim($ret);
    }

    public static function sortByIndicator1()
    {
        return function (\File_MARC_Data_Field $a, \File_MARC_Data_Field $b) {
            $aInd1 = $a->getIndicator(1);
            if ($aInd1 === " ") {
                $aInd1 = "1";
            }
            $bInd1 = $b->getIndicator(1);
            if ($bInd1 === " ") {
                $bInd1 = "1";
            }
            return $aInd1 >= $bInd1 ? -1 : 1;
        };
    }

    public static function subStrTill($str, $signs)
    {
        foreach ($signs as $sign) {
            if (($pos = strpos($str, $sign)) !== false) {
                $str = substr($str, 0, $pos);
            }
        }
        return $str;
    }

    public static function utf8_encode($str) {

        $enc = mb_detect_encoding($str);
        return $enc !== self::DEFAULT_CHARSET ? mb_convert_encoding($str, self::DEFAULT_CHARSET) : $str;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @param $subFieldCode
     * @return string
     */
    public static function getSubField($field, $subFieldCode)
    {
        return !$field->getSubfield($subFieldCode) ? '' : trim($field->getSubfield($subFieldCode)->getData());
    }
}
