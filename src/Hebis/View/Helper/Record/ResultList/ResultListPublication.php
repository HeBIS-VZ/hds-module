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
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class ResultListPublication extends AbstractRecordViewHelper
{

    /**
     *
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /* wenn 264 Indikator 2 = 1, dann anzeigen wie folgt:
        264 $a_:_$b,_$c
        Wenn 533 $d vorhanden, dann wie folgt anzeigen:
        264 $a_:_$b,_533 $d

        Bei mehr als eine 264 mit Indikator 2 = 1 nur eine anzeigen; Priorisierung wie folgt:

        264 Indikator 1 = 3 und Indikator 2 = 1
        264 Indikator 1 = # und Indikator 2 = 1

        Kommen $a und/oder $b mehrfach vor, dann Trennzeichen: ";_" (in Worten: Semikolon Blank)
        */
        $ret = "";
        $id = $record->getUniqueID();

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $_264__ = $this->filterByIndicator($marcRecord->getFields('264'), 1, "3");
        if (empty($_264__)) {
            $_264__ = $this->filterByIndicator($marcRecord->getFields('264'), 1, "");
        }

        return $this->generateContents($record, $_264__);

    }

    /**
     * @param $field
     * @param string $_533_d
     * @return string
     */
    protected function concatSubfields($field, $_533_d)
    {
        $ret = "";
        $a = $this->getSubFieldDataOfGivenField($field, 'a');
        $b = $this->getSubFieldDataOfGivenField($field, 'b');
        $c = $this->getSubFieldDataOfGivenField($field, 'c');

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