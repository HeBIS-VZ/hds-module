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
        $_505 = $marcRecord->getFields('505');


        /** @var \File_MARC_Data_Field $field */
        $i = 0;
        $j = 0;
        foreach ($_249 as $field) {
            $a_249 = $this->removeControlSigns(Helper::getSubFieldDataOfGivenField($field, 'a'));
            $b_249 = $this->removeControlSigns(Helper::getSubFieldDataOfGivenField($field, 'b'));
            $c_249 = $this->removeControlSigns(Helper::getSubFieldDataOfGivenField($field, 'c'));
            $v_249 = $this->removeControlSigns(Helper::getSubFieldDataOfGivenField($field, 'v'));


            if ($a_249) {
                $arr[] = $a_249;
                if ($v_249) {
                    $arr[$i] .= ' / ' . $v_249;
                }
            }

            if ($b_249) {
                $arr[] = $b_249;
                if ($c_249) {
                    $arr[$i] .= ' / ' . $c_249;
                }
            }
            $i++;
        }

        foreach ($_505 as $field) {
            $a_505 = $this->removeControlSigns(Helper::getSubFieldDataOfGivenField($field, 'a'));
            $t_505 = $this->removeControlSigns(Helper::getSubFieldDataOfGivenField($field, 't'));
            $r_505 = $this->removeControlSigns(Helper::getSubFieldDataOfGivenField($field, 'r'));


            if ($a_505) {
                $arr[] = $a_505;
                if ($t_505) {
                    $arr[$j] .= ' ' . $t_505;
                }
            }
            else if ($t_505) {
                    $arr[] = $t_505;
            }
            if ($r_505) $arr[$j] .= ' / ' . $r_505;

            $j++;
        }

        return implode("<br />", $arr);
    }
}