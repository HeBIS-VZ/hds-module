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
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use Hebis\RecordDriver\SolrMarc;


/**
 * Class ResultListPersonalName
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class ResultListPersonalName extends AbstractRecordViewHelper
{

    /**
     *
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {

        /* 100 $a_$b_<$c>_($e,_$e)
        sofern vorhanden, sonst zuerst genannte 700 mit Indikator 2 = #:
        700 $a_$b_<$c>_($e,_$e) */

        /** @var \File_MARC $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $aut = $this->getFieldContentsByFieldNo($marcRecord, 100);

        if (empty($aut)) {
            $f700 = $marcRecord->getFields(700);
            if (!empty($f700)) {
                if (!empty($filteredFields = $this->filterByIndicator($f700, 2, " "))) {
                    $addedEntryPN = $this->getFieldContents(current($filteredFields));
                    $aut .= (!empty($addedEntryPN)) ? "$addedEntryPN" : "";
                }
            }
        }
        return $aut;
    }

    /**
     * @param $marcRecord
     * @param $fieldNo
     * @return array
     */
    protected function getFieldContentsByFieldNo($marcRecord, $fieldNo)
    {
        /** @var \File_MARC_Data_Field $field */
        $field = $marcRecord->getField($fieldNo);
        return $this->getFieldContents($field);
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    protected function getFieldContents($field)
    {
        $ret = "";
        $a = $this->getSubFieldDataOfGivenField($field, 'a');
        $b = $this->getSubFieldDataOfGivenField($field, 'b');
        $c = $this->getSubFieldDataOfGivenField($field, 'c');
        $eArray = !is_bool($field) ? $field->getSubfields("e") : [];

        $ret .= $a ? $a : "";
        $ret .= $b ? " $b" : "";
        $ret .= $c ? " &lt;$c&gt;" : "";

        if (count($eArray) > 0) {

            $ret .= " (";
            $i = 0;
            /** @var \File_MARC_Subfield $e_ */
            foreach ($eArray as $e_) {
                $e = $e_->getData();
                if ($i++ > 0) {
                    $ret .= ", ";
                }

                $ret .= $e ? htmlentities($e) : "";
            }
            $ret .= ")";
        }

        return $ret;
    }
}
