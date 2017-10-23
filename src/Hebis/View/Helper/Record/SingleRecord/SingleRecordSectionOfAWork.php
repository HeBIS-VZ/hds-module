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
 * Class SingleRecordSectionOfAWork
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordSectionOfAWork extends AbstractRecordViewHelper
{
    protected $record;

    public function __invoke(SolrMarc $record)
    {
        $this->record = $record;
        return $this;
    }

    public function render()
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $this->record->getMarcRecord();
        $leader = $marcRecord->getLeader();

        $char = $leader{19};
        $arr = [];

        if (preg_match("/\s/", $char)) {
            $arr = $this->getNp();
        }
        return implode("<br />", $arr);
    }

    /**
     * @return array
     */
    public function getNp()
    {
        $arr = [];
        $fields = $this->record->getMarcRecord()->getFields('245');


        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {

            $nps = $this->normalizeArrayStructure($this->getSubFieldsDataArrayOfField($field, ['n', 'p']));
            $first = array_shift($nps);
            $second = array_shift($nps);

            for ($i = 0; $i < count($first); ++$i) {
                $str = $first[$i];
                if (isset($second) && isset($second[$i])) {
                    $str .= ". " . $second[$i];
                }
                $arr[] = $str;
            }
        }
        return $arr;
    }

    private function normalizeArrayStructure($arr)
    {
        $array = [];
        foreach ($arr as $key => $value) {
            if (!is_array($value)) {
                $array[$key] = [$value];
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }
}