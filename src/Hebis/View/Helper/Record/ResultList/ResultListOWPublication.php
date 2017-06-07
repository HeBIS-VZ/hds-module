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

namespace Hebis\View\Helper\Record\ResultList;

use Hebis\Marc\Helper;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use Hebis\RecordDriver\SolrMarc;


/**
 * Class ResultListPublicationDistribution
 * @package Hebis\View\Helper\Record
 *
 * @author Claudia Grote <grote@hebis.uni-frankfurt.de>
 */
class ResultListOWPublication extends AbstractRecordViewHelper
{

    /**
     *
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $_880_264_ = array();
        $_880__ = $marcRecord->getFields('880');
        foreach ($_880__ as $_880) {
            $_880_6 = empty($_880) ? "" : Helper::getSubFieldDataOfGivenField($_880, '6');
            if (strncmp("264", $_880_6, 3) == 0) {
                $_880_264_[] = $_880;
            }
        }

        return $this->generateContents($record, $_880_264_);

    }

    /**
     * @param $field
     * @param string $_533_d
     * @return string
     */
    protected function concatSubfields($field, $_533_d)
    {
        $ret = "";
        $a = Helper::getSubFieldDataOfGivenField($field, 'a');
        $b = Helper::getSubFieldDataOfGivenField($field, 'b');

        $ret .= !empty($a) ? "$a" : "";
        $ret .= !empty($b) ? " : $b" : ""; //append $b

        return $ret;
    }

    /**
     * @param SolrMarc $record
     * @return string
     */
    protected function generateContents(SolrMarc $record, $_264__)
    {
        $ret = "";
        $_264 = current($_264__);

        $_533d = Helper::getSubFieldDataOfField($record, '533', 'd');
        $_264_ = empty($_264) ? [] : $this->getSubFieldsDataArrayOfField($_264, ['a', 'b']);

        if (!empty($_533d)) {
            $ret .= implode(" : ", $_264_);
            $ret .= !empty($ret) ? ", $_533d" : $_533d;
        } else {
            $_264c = empty($_264) ? "" : Helper::getSubFieldDataOfGivenField($_264, 'c');

            $_264_ = $this->flattenSubfields($_264_, "; ");

            $ret .= implode(" : ", $_264_);
            $ret .= empty($_264c) ? "" : !empty($ret) ? ", $_264c" : $_264c;
        }

        return $ret;
    }

    private function flattenSubfields($field, $delimiter)
    {
        $field_ = [];

        foreach ($field as $subfieldKey => $subfieldValue) {
            if (is_array($subfieldValue)) {
                $field_[$subfieldKey] = implode($delimiter, $subfieldValue);
            } else {
                $field_[$subfieldKey] = $subfieldValue;
            }
        }

        return $field_;
    }

}