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

    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $leader = $marcRecord->getLeader();

        $char = $leader{19};
        $arr = [];

        if (preg_match("/\s/", $char)) {
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
        $arr = [];
        $fields = $marcRecord->getFields('245');
        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {

            /** @var \File_MARC_Subfield $subField */
            foreach ($field->getSubfields() as $subField) {
                $key = $subField->getCode();

                switch ($key) {
                    case 'n':
                        $n = htmlentities($subField->getData());
                        break;
                    case 'p':
                        $p = htmlentities($subField->getData());
                        break;
                }
                $np = !empty($n) ? "$n. " : "";
                $np .= !empty($p) ? "$p" : "";

                if (!empty($np)) {
                    $arr[] = $np;
                }
            }
        }
        return $arr;
    }
}