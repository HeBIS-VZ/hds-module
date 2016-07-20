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

use File_MARC_Record;

use Hebis\RecordDriver\SolrMarc;
use Zend\View\Helper\AbstractHelper;

/**
 *
 * @package Hebis\View\Helper
 */
class SingleRecordUniformTitle extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /** @var File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $ret = "";
        $fields = [];

        foreach (['240', '243', '730'] as $fieldCode) {
            $fields[] = $marcRecord->getField($fieldCode);
        }

        $a = $g = $r = false;
        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            if ($field) {
                $a = $this->getSubFieldDataOfGivenField($field, 'a');
                $g = $this->getSubFieldDataOfGivenField($field, 'g');
                $r = $this->getSubFieldDataOfGivenField($field, 'r');
                $ret .= $a ? $a : "";
                $ret .= $g ? " &lt;$g&gt;" : "";
                $ret .= $r ? " &lt;$r&gt;" : "";
                $ret .= "<br />\n"; //newline
                $a = $g = $r = false;
            }
        }

        return $ret;
    }

    /**
     * removes @ at the beginning of the string or an @ where a blank as prefix exist and followed by a word a digit
     * @param $string
     * @return mixed
     */
    public function removeSpecialChars($string)
    {
        $string = preg_replace('/^@/', "", $string);
        return preg_replace('/\s\@([\w\däöü])/', " $1", $string);
    }
}