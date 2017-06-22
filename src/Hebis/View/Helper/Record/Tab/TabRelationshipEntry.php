<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an 
 * extension of the open source library search engine VuFind, that 
 * allows users to search and browse beyond resources. More 
 * Information about VuFind you will find on http://www.vufind.org
 * 
 * Copyright (C) 2017 
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

namespace Hebis\View\Helper\Record\Tab;

use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;


/**
 * Class TabRelationshipEntry
 * @package Hebis\View\Helper\Record\Tab
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class TabRelationshipEntry extends AbstractRecordViewHelper
{
    private $record;

    public function __invoke(SolrMarc $record)
    {
        $this->record = $record;

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $ret = [];
        foreach ($marcRecord->getFields(770) as $field) {
            $ret[] = $this->generate7xx($field);
        }

        foreach ($marcRecord->getFields(772) as $field) {
            $ret[] = $this->generate7xx($field);
        }

        foreach ($marcRecord->getFields(777) as $field) {
            $ret[] = $this->generate7xx($field);
        }

        foreach ($marcRecord->getFields(787) as $field) {
            $ret[] = $this->generate7xx($field);
        }

        return implode("<br />\n", $ret);
    }

    private function generate7xx($field7xx)
    {
        $ret = "";

        $w_ = array_filter($field7xx->getSubfields('w'), function (\File_MARC_Subfield $elem) {
            return strpos($elem->getData(), "(DE-603)") !== false;
        });

        $i = $this->getSubField($field7xx, "i");
        $a = $this->getSubField($field7xx, "a");
        $t = $this->getSubField($field7xx, "t");
        $b = $this->getSubField($field7xx, "b");
        $d = $this->getSubField($field7xx, "d");
        $g = $this->getSubField($field7xx, "g");
        $h = $this->getSubField($field7xx, "h");
        $z = $this->getSubFieldsDataArrayOfField($field7xx, ["z"]);
        $o = $this->getSubField($field7xx, "o");
        $x = $this->getSubField($field7xx, "x");


        $ret .= !empty($i) ? "$i" : "";

        if (!empty($ret)) {
            if (empty($a)) {
                $ret .= ": ";
            } else {
                $ret .= ". $a";
            }
        }

        $ltext = "";
        $ltext .= !empty($t) ? "$t" : "$t";
        $ltext .= !empty($b) ? " - $b" : "";
        $ltext .= !empty($d) ? " - $d" : "";
        $ltext .= !empty($g) ? " - $g" : "";
        $ltext .= !empty($h) ? " - $h" : "";
        $ltext .= !empty($z) ? " - ISBN " . implode(" ; ISBN ", $z) : "";
        $ltext .= !empty($o) ? " - $o" : "";
        $ltext .= !empty($x) ? " - $x" : "";

        if (!empty($w_)) {
            $ret .= $this->getView()->ppnLink()->getLink(
                htmlentities($ltext),
                $this->removePrefix($w_[0]->getData(), "(DE-603)"),
                ["backlink" => $this->record->getPPN()]
            );
        } else {
            $ret .= $ltext;
        }

        return $ret;
    }

}