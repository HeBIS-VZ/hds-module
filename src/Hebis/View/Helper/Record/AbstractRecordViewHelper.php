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

namespace Hebis\View\Helper\Record;

use Hebis\Marc\Helper;
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\FieldArray;
use \File_MARC_Data_Field;
use Zend\View\Helper\AbstractHelper;

/**
 * Class AbstractRecordViewHelper
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class AbstractRecordViewHelper extends AbstractHelper
{
    use FieldArray;

    /** Search Link Pattern Full Title */
    const URL_FULL_TITLE_SEARCH_PATTERN = 'Search/Results?lookfor0%5B%5D=';
    const URL_FULL_TITLE_SEARCH_PATTERN_SUFFIX = '&type0%5B%5D=title';


    const URL_AUTHOR_SEARCH_PATTERN = 'Search/Results?lookfor0[]=&type0[]=author&lastposition';


    const URL_SEARCH_PPN = 'RecordFinder/HEB%s';

    const URL_SHOW_ALL = 'Results?lookfor=%d&type=part_of&sort=pub_date_max+desc';


    /**
     * checks if subField exists, if true it returns the subField containing data
     * otherwise it returns an empty string
     *
     * @param File_MARC_Data_Field $field
     * @param string $subFieldCode
     * @return string
     */
    protected function getSubField(File_MARC_Data_Field $field, $subFieldCode)
    {
        return !$field->getSubfield($subFieldCode) ? '' : trim($field->getSubfield($subFieldCode)->getData());
    }

    /**
     * returns the data of the subField if field and subField exists, otherwise false
     *
     * @deprecated use \Hebis\Marc\Helper::getSubFieldDataOfField instead
     *
     * @param SolrMarc $record
     * @param $fieldCode
     * @param $subFieldCode
     * @return bool|string
     */
    protected function getSubFieldDataOfField(SolrMarc $record, $fieldCode, $subFieldCode)
    {
        return Helper::getSubFieldDataOfField($record, $fieldCode, $subFieldCode);
    }

    /**
     * @param File_MARC_Data_Field $field
     * @param array $subFieldCodes
     * @return array
     */
    protected function getSubFieldsDataOfField(\File_MARC_Data_Field $field, $subFieldCodes = [])
    {
        $arr = [];

        foreach ($subFieldCodes as $subFieldCode) {
            $ar = $this->getSubFieldDataArrayOfGivenField($field, $subFieldCode);
            for ($i = 0; $i < count($ar); ++$i) {
                $arr[$i][$subFieldCode] = $ar[$i];
            }
        }

        return $arr;
    }

    /**
     * @param File_MARC_Data_Field $field
     * @param array $subFieldSubFieldCodes
     * @return array
     */
    protected function getSubFieldsDataArrayOfField(\File_MARC_Data_Field $field, $subFieldSubFieldCodes = [])
    {
        $arr = [];

        foreach ($subFieldSubFieldCodes as $subFieldCode) {
            $ar = $this->getSubFieldDataArrayOfGivenField($field, $subFieldCode);
            if (empty($ar)) {
                continue;
            }
            $arr[$subFieldCode] = count($ar) > 1 ? $ar : $ar[0];
        }

        return $arr;
    }

    /**
     * if field of type \File_MARC_Data_Field and it has at least one subField with $subFieldCode this function returns the data of
     * all subFields in an array, otherwise false
     *
     * @param \File_MARC_Data_Field|bool $field
     * @param $subFieldCode
     * @return bool|array
     */
    protected function getSubFieldDataArrayOfGivenField($field, $subFieldCode)
    {

        if ($field && $field instanceof \File_MARC_Data_Field) {
            $arr = [];

            /** @var \File_MARC_Subfield $subField */
            foreach ($field->getSubfields($subFieldCode) as $subField) {

                if ($subField) {
                    $arr[] = htmlentities($subField->getData());
                }
            }
            return $arr;
        }

        return false;
    }

    /**
     * returns either array in form $arr[code] => data or false, if field empty or has no subfields
     * @param File_MARC_Data_Field $field
     * @return array|bool
     */
    protected function getSubfieldsAsArray(\File_MARC_Data_Field $field)
    {

        if (!empty($field) && !empty($field->getSubfields())) {
            $arr = [];
            /** @var \File_MARC_Subfield $subfield */
            foreach ($field->getSubfields() as $subfield) {
                $arr[$subfield->getCode()] = $subfield->getData();
            }
            return $arr;
        }
        return false;
    }


    /**
     * if field of type \File_MARC_Data_Field and it has a subField with $subFieldCode this function returns the data
     * string of the subField, otherwise false.
     *
     * @deprecated use \Hebis\Marc\Helper::getSubFieldDataOfGivenField instead
     *
     * @param $field
     * @param $subFieldCode
     * @return bool|string
     */
    protected function getSubFieldDataOfGivenField($field, $subFieldCode)
    {
        return Helper::getSubFieldDataOfGivenField($field, $subFieldCode);
    }

    /**
     * if $needle is a substring at the beginning of $haystack this function returns the haystack truncated to $needle,
     * otherwise it returns the full haystack
     *
     * @param string $haystack
     * @param string $needle
     * @return string
     */
    protected function removePrefix($haystack, $needle)
    {
        if (strpos($haystack, $needle) === 0) {
            return substr($haystack, strlen($needle));
        }
    }

    /**
     * Returns an array containing sub field data of $subFieldCode from all fields of $fieldCode comprised in given
     * $record
     *
     * @param SolrMarc $record
     * @param string $fieldCode
     * @param string $subFieldCode
     * @return array
     */
    protected function getSubFieldsOfFieldType(SolrMarc $record, $fieldCode, $subFieldCode)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $fields = $marcRecord->getFields($fieldCode);
        $arr = [];

        foreach ($fields as $field) {
            $subField = $this->getSubFieldDataArrayOfGivenField($field, $subFieldCode);
            if ($subField) {
                $arr[] = $subField;
            }
        }
        return $arr;
    }

    /**
     *
     * Generates Link including basePath
     *
     * @param string $href
     * @param string $title
     * @param string $linkText
     * @return string
     */
    protected function generateLink($href, $title, $linkText)
    {
        $basePath = $this->getView()->basePath();
        if (!empty($basePath)) { //basePath not part of href
            if (strpos($href, $basePath) !== 0) {
                $href = $this->getView()->basePath() . "/" . $href; //prepend bathPath
            }
        }
        return sprintf('<a href="%s" title="%s">%s</a>', $href, $title, $linkText);
    }

    /**
     * returns $str without control signs i.e. '@'
     * @param string $str
     * @return string
     */
    protected function removeControlSigns($str)
    {
        $len = strlen($str);
        if (strpos($str, '@') === 0) {
            $str = substr($str, 1, $len - 1);
        }
        $ret = str_replace(" @", " ", $str);
        return $ret;
    }

    /**
     * @param array $_264s
     * @param string|int $ind indicator name
     * @param string|int $x indicator value
     * @return array
     */
    protected function filterByIndicator(array $_264s, $ind, $x)
    {
        return array_filter($_264s, function ($a) use ($ind, $x) {
            /** @var \File_MARC_Data_Field $a */
            if (empty($x) || $x == " ") {
                return ord($a->getIndicator($ind)) == 32;
            }
            return $a->getIndicator($ind) === $x;
        });
    }

    /**
     * removes @ at the beginning of the string or an @ where a blank as prefix exist and followed by a word a digit
     * @param $string
     * @return mixed
     */
    public function removeSpecialChars($string)
    {
        $string = preg_replace('/^@/', "", $string);
        return preg_replace('/\s\@([\w\däöü])/i', " $1", $string);
    }
}
