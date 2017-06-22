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
 * Class ReproductionNote
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordReproductionNote extends AbstractRecordViewHelper
{

    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();


        /* Nur anzeigen, wenn Marc Leader Pos. 6 und 7 NICHT [a][s]  (= NICHT Materialart journal or electronic).
           Wenn 533 $c nicht besetzt:
           530 $a_533 $b_:_583 $h,_533 $d._-_533 $e._-_(533 $f)
           SONST: 530 $a_533 $b_:_533 $c,_533 $d._-_533 $e._-_(533 $f) */

        if (substr($marcRecord->getLeader(), 6, 2) === "as") {
            return "";
        }

        $str = "";
        $_533 = $marcRecord->getField('533');
        $_530_a = Helper::getSubFieldDataOfField($record, '530', 'a');
        $_533_b = Helper::getSubFieldDataOfField($record, '533', 'b');
        $_533_d = Helper::getSubFieldDataOfField($record, '533', 'd');
        $_533_e = Helper::getSubFieldDataOfField($record, '533', 'e');
        $_533_f = Helper::getSubFieldDataOfField($record, '533', 'f');

        if (empty($_533) || (!empty($_533) && empty($_533->getSubfields('c')))) {
            $_583_h = Helper::getSubFieldDataOfField($record, '583', 'h');
            $str .= !empty($_530_a) ? "$_530_a" : "";
            $str .= !empty($_533_b) ? " $_533_b" : "";
            $str .= !empty($_583_h) ? ": $_583_h" : "";

        } else {
            $_533_c = Helper::getSubFieldDataOfField($record, '533', 'c');
            $str .= !empty($_533_b) ? "$_530_a $_533_b : " : "$_530_a ";
            $str .= !empty($_533_c) ? $_533_c : "";
        }

        $str .= !empty($_533_d) ? ", $_533_d" : "";
        $str .= !empty($_533_e) ? ". - $_533_e" : "";
        $str .= !empty($_533_f) ? ". - ($_533_f)" : "";

        return $str;
    }
}