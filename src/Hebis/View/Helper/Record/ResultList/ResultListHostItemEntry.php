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
 * Class ResultListHostItemEntry
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class ResultListHostItemEntry extends AbstractRecordViewHelper
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

        /* 773 $a._$t._-_$b._-_$d._-_$g._-_$h._-_$z._-_$o._-_$x */

        /** @var \File_MARC_Data_Field $field */
        $fields = $marcRecord->getFields(773);
        $arr = [];
        $str = "";
        foreach ($fields as $field) {
            /** @var \File_MARC_Subfield $subfield */
            foreach ($field->getSubfields() as $subfield) {
                if (!in_array($subfield->getCode(), ['a', 't', 'b', 'd', 'g', 'h', 'z', 'o', 'x'])) {
                    continue;
                }
                switch ($subfield->getCode()) {
                    case 'a':
                        $str .= $subfield->getData().". ";
                        break;
                    case 'x':
                        $arr[] = 'ISBN '.htmlentities($subfield->getData());
                        break;
                    case 'z':
                        $arr[] = 'ISBN '.htmlentities($subfield->getData());
                        break;
                    default:
                        $arr[] = htmlentities($subfield->getData());
                }
            }
        }
        return $str.implode(". - ", $arr);
    }
}