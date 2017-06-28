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

/**
 * Class SingleRecordPartOfAWork
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordPartOfAWork extends AbstractRecordViewHelper
{
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $leader = $marcRecord->getLeader();

        $char = $leader{19};
        $arr = [];

        if (preg_match("/[c]/", $char)) {
            $arr = $this->createOutput($marcRecord, $arr);
        }
        return implode("<br />", $arr);
    }

    /**
     * @param $marcRecord
     * @param $arr
     * @return array
     */
    protected function createOutput($marcRecord, $arr)
    {
        $ret = [];
        $fields = $marcRecord->getFields('245');


        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            $arr = []; //$n._$p pairs
            $n = $p = "";
            /** @var \File_MARC_Subfield $subField */
            foreach ($field->getSubfields() as $subField) {
                $key = $subField->getCode();
                switch ($key) {
                    case 'n':
                        if (strpos($subField->getData(), "[...]") === false) {
                            $n = htmlentities($this->removeControlSigns($subField->getData()));
                        }
                        break;
                    case 'p':
                        $p = htmlentities($this->removeControlSigns($subField->getData()));
                        break;
                }

                if (!empty($n)) {
                    $arr[] = trim($n);
                    $n = "";
                }
                if (!empty($p)) {
                    $arr[] = trim($p);
                    $p = "";
                }

                if ($key !== "n" && !empty($arr)) {
                    $ret[] = trim(implode(". ", $arr));
                    $arr = [];
                }
            }
        }
        return $ret;
    }
}