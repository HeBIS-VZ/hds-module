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
        $id = $record->getUniqueID();
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /* 773 $a._$t._-_$b._-_$d._-_$g._-_$h._-_$z._-_$o._-_$x */

        /** @var \File_MARC_Data_Field $field */
        $fields = $marcRecord->getFields(773);

        return $this->output($fields);
    }

    protected function output($fields)
    {
        $ret = "";

        foreach ($fields as $field) {

            $ret .= !empty($a = $field->getSubfield('a')) ? htmlentities($a->getData()).". " : "";

            $ret .= $this->generateSubfields($field);
        }
        return $ret;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     * @internal param $ret
     */
    protected function generateSubfields(\File_MARC_Data_Field $field)
    {

        $arr = [];

        $w = $this->getAssociatedPPNs($field);
        $subFields = $field->getSubfields();

        /** @var \File_MARC_Subfield $subField */
        foreach ($subFields as $subField) {
            $c = $subField->getCode();
            $v = $subField->getData();

            if (!in_array($c, ['t', 'b', 'd', 'g', 'h', 'z', 'o', 'x'])) {
                continue;
            }

            switch ($c) {
                case 'x':
                    $arr[] = 'ISBN ' . htmlentities($v);
                    break;
                case 'z':
                    $arr[] = 'ISBN ' . htmlentities($v);
                    break;
                case 't': //search link?
                    if (!empty($w)) {
                        $arr[] = $this->addLink($subField, current($w));
                    } else {
                        $arr[] = htmlentities($v);
                    }
                    break;
                default:
                    $arr[] = htmlentities($v);
                    break;
            }
        }
        return implode(". - ", $arr);
    }

    /**
     * @param \File_MARC_Subfield $subfield
     * @param \File_MARC_Subfield $w
     * @return string
     */
    protected function addLink($subfield, $w)
    {
        return htmlentities($subfield->getData());
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return array
     */
    protected function getAssociatedPPNs(\File_MARC_Data_Field $field)
    {

        $w = $field->getSubfields("w");

        $w = array_filter($w, function ($field) {
            return strpos($field->getData(), "(DE-603)") !== false;
        });

        return $w;
    }
}