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

use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use Hebis\RecordDriver\SolrMarc;

/**
 * Class SingleRecordOtherEditionEntry
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordOtherEditionEntry extends AbstractRecordViewHelper
{
    /**
     * @var SolrMarc
     */
    protected $record;

    public function __invoke(SolrMarc $record)
    {
        $this->record = $record;

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $id = $record->getUniqueID();

        /** @var array $fields */
        $fields = $marcRecord->getFields('775');

        $arr = [];
        foreach ($fields as $field) {
            /* Nur anzeigen wenn Indikator 1 = 0 und Indikator 2 = 8: */
            $ind1 = $field->getIndicator(1);
            $ind2 = $field->getIndicator(2);
            if ($ind1 == "0" && $ind2 == "8") {
                $arr[] = $this->generateContents($field);
            }
        }

        /** @var array $fields */
        $fields = $marcRecord->getFields('776');

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            /* Nur anzeigen wenn Indikator 1 = 0 und Indikator 2 = 8: */
            $ind1 = $field->getIndicator(1);
            $ind2 = $field->getIndicator(2);
            if ($ind1 == "0" && $ind2 == "8") {
                $w_ = array_filter($field->getSubfields('w'), function (\File_MARC_Subfield $elem) {
                    return strpos($elem->getData(), "(DE-603)") !== false;
                });
                if (!empty($w_)) {
                    $str = $this->getView()->ppnLink()->getLink(
                        $this->generateContents($field),
                        $this->removePrefix($w_[0]->getData(), "(DE-603)"),
                        ["backlink" => $this->record->getPPN()]
                    );
                } else {
                    $str = $this->generateContents($field);
                }


                $arr[] = $this->generatePrefix($field).$str;
            }
        }

        return implode("<br />", $arr);
    }

    protected function generatePrefix($field)
    {
        $subFieldKeys = ['t', 'b', 'd', 'g', 'h', 'z', 'o', 'x'];
        $subFields = $this->getSubfieldsAsArray($field);

        $prefix = "";
        /* Wenn kein $a vorhanden, dann nach Inhalt aus
        $i bzw. $n ":_" (Doppelpunkt Blank) ergänzen statt
        vorgegebenem Deskriptionszeichen */
        if (!array_key_exists('a', $subFields)) {
            if (array_key_exists('i', $subFields) && array_key_exists('n', $subFields)) {
                $prefix .= htmlentities($subFields['i']) . " " . htmlentities($subFields['n']) . ": ";
            } else {
                if (array_key_exists('i', $subFields)) {
                    $prefix .= htmlentities($subFields['i']) . ": ";
                } else {
                    if (array_key_exists('n', $subFields)) {
                        $prefix .= htmlentities($subFields['n']) . ": ";
                    }
                }
            }
        } else {
            if (array_key_exists('i', $subFields) && array_key_exists('n', $subFields)) {
                $prefix .= htmlentities($subFields['i']) . " " . htmlentities($subFields['n']) . ": ";
            } else {
                if (array_key_exists('i', $subFields)) {
                    $prefix .= htmlentities($subFields['i']) . ": ";
                } else {
                    if (array_key_exists('n', $subFields)) {
                        $prefix .= htmlentities($subFields['n']) . ": ";
                    }
                }
            }
            $prefix .= htmlentities($subFields['a']) . ". "; // a nachtragen
        }
        return $prefix;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    protected function generateContents($field)
    {
        $subFieldKeys = ['t', 'b', 'd', 'g', 'h', 'z', 'o', 'x'];
        $subFields = $this->getSubfieldsAsArray($field);

        $arr = [];

        foreach ($subFieldKeys as $pos => $key) {
            switch ($key) {
                case 'a':
                    if (!empty($w_)) { // mit link

                        $linkText = $subFields[$key] . ". ";
                        if (array_key_exists("t", $subFields)) { //mit link?
                            $linkText .= htmlentities($subFields['t']);
                            $tCalled = true;
                        }

                        $arr[] = $str;
                    } else {
                        $arr[] = htmlentities($subFields[$key]);
                    }
                    break;

                case 'z':
                    if (array_key_exists($key, $subFields)) {
                        $arr[$key] = "ISBN " . htmlentities($subFields[$key]);
                    }
                    break;

                case 'x':
                    if (array_key_exists($key, $subFields)) {
                        $arr[$key] = "ISSN " . htmlentities($subFields[$key]);
                    }
                    break;

                case 't':
                    if (array_key_exists($key, $subFields)) {
                        $str = htmlentities($subFields[$key]);
                        $arr[$key] = $str;
                    }
                    break;

                default:
                    if (array_key_exists($key, $subFields)) {
                        $arr[$key] = htmlentities($subFields[$key]);
                    }
            }
        }

        return implode(". - ", $arr);
    }

    protected function link($w)
    {
        $ppn = $this->removePrefix($w, "(DE-603)");
        $ppnLink = $this->getView()->ppnLink()->getLink($ppn);
        return $ppnLink;
    }
}