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


use \File_MARC_Data_Field;
use Hebis\RecordDriver\SolrMarc;
use Zend\View\Helper\AbstractHelper;

class AbstractRecordViewHelper extends AbstractHelper
{
    use FieldArray;

    /** Search Link Pattern Full Title */
    const URL_FULL_TITLE_SEARCH_PATTERN = 'Search/Results?lookfor0[]=%s&type0[]=fulltitle';


    const URL_AUTHOR_SEARCH_PATTERN = 'Search/Results?lookfor0[]=&type0[]=author&lastposition';


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
     * @param SolrMarc $record
     * @param $fieldCode
     * @param $subFieldCode
     * @return bool|string
     */
    protected function getSubFieldDataOfField(SolrMarc $record, $fieldCode, $subFieldCode)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $field */
        $field = $marcRecord->getField($fieldCode);

        return $this->getSubFieldDataOfGivenField($field, $subFieldCode);
    }

    /**
     * if field of type \File_MARC_Data_Field and it has a subField with $subFieldCode this function returns the data
     * string of the subField
     *
     * @param \File_MARC_Data_Field|bool $field
     * @param $subFieldCode
     * @return bool|string
     */
    protected function getSubFieldDataOfGivenField($field, $subFieldCode)
    {

        if ($field && $field instanceof \File_MARC_Data_Field) {

            /** @var \File_MARC_Subfield $subField */
            $subField = $field->getSubfield($subFieldCode);

            if ($subField) {
                return $subField->getData();
            }
        }

        return false;
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
            $subField = $this->getSubFieldDataOfGivenField($field, $subFieldCode);
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
        $href = $this->getView()->basePath() . "/" . $href;
        return sprintf('<a href="%s" title="%s">%s</a>', $href, htmlentities($title), htmlentities($linkText));
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
            $str = substr($str, 1, $len-1);
        }
        $ret = str_replace(" @", " ", $str);
        return $ret;
    }
}