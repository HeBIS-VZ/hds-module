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
 * Class PrecedingSucceedingEntry
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordPrecedingSucceedingEntry extends SingleRecordOtherEditionEntry
{

    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        $id = $record->getUniqueID();
        $marcRecord = $record->getMarcRecord();

        $arr = [];

        $rda = $this->getSubFieldDataOfField($record, '040', 'e') === "rda";
        $_780_ = $marcRecord->getFields(780);

        foreach ($_780_ as $field) {
            if ($rda) {
                $arr[] .= $this->generateContents($field);
            } else {
                $arr[] .= $this->generateRak($field);
            }
        }

        $_785_ = $marcRecord->getFields(785);

        foreach ($_785_ as $field) {
            if ($rda) {
                $arr[] .= $this->generateContents($field);
            } else {
                $arr[] .= $this->generateRak($field);
            }
        }

        return implode("<br />", $arr);
    }

    private function generateRak($field)
    {
        $subFields = $this->getSubfieldsAsArray($field);

        $w_ = array_filter($field->getSubfields('w'), function (\File_MARC_Subfield $elem) {
            return strpos($elem->getData(), "(DE-603)") !== false;
        });

        $ret = "";
        /* Wenn kein $a vorhanden, dann nach Inhalt aus
        $i bzw. $n ":_" (Doppelpunkt Blank) ergänzen statt
        vorgegebenem Deskriptionszeichen */

        if (array_key_exists('i', $subFields)) {
            $ret .= htmlentities($subFields['i']) . ": ";
        }

        if (array_key_exists('a', $subFields) && array_key_exists('t', $subFields) && !empty($w_)) {
            $link = $this->link($w_[0]->getData());
            $ret .= '<a href="' . $link . '">';
            $ret .= htmlentities($subFields['a']) . ": ";
            $ret .= htmlentities($subFields['t']);
            $ret .= '</a>';
        } else {
            if (array_key_exists('a', $subFields)) {
                if (!empty($w_)) {
                    $link = $this->link($w_[0]->getData());
                    $ret .= '<a href="' . $link . '">' . htmlentities($subFields['a']) . '</a>' . ": ";
                } else {
                    $ret .= htmlentities($subFields['a']) . ": ";
                }
            }

            if (array_key_exists('t', $subFields)) {
                if (!empty($w_)) {
                    $link = $this->link($w_[0]->getData());
                    $ret .= '<a href="' . $link . '">' . htmlentities($subFields['t']) . '</a>';
                } else {
                    $ret .= htmlentities($subFields['t']);
                }
            }
        }
        return $ret;
    }

    /**
     * Overrides getSubfieldsAsArray from AbstractRecordViewHelper in order to handle repeatable subfield "z"
     *
     * @param \File_MARC_Data_Field $field
     * @return array|bool
     */
    protected function getSubfieldsAsArray(\File_MARC_Data_Field $field)
    {

        if (!empty($field) && !empty($field->getSubfields())) {
            $arr = [];
            /** @var \File_MARC_Subfield $subfield */
            foreach ($field->getSubfields() as $subfield) {
                if ($subfield->getCode() === "z" && array_key_exists($subfield->getCode(), $arr)) {
                    $arr[$subfield->getCode()] .= " ; " . $subfield->getData();
                } else {
                    $arr[$subfield->getCode()] = $subfield->getData();
                }
            }
            return $arr;
        }
        return false;
    }

    protected function link($w)
    {
        return $this->getView()->basePath() . '/RecordFinder/HEB' . $this->removePrefix($w, "(DE-603)");
    }
}