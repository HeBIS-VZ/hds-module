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

namespace Hebis\Csl\MarcConverter;

use \Hebis\Csl\Model\Record as Thesis;

class ThesisConverter
{

    use SubfieldsTrait;

    public static function convert(\File_MARC_Record $record)
    {
        $thesis = new Thesis();

        $thesis->setAuthor(Name::getAuthor($record));
        $thesis->setCollectionNumber(Record::getCollectionNumber($record));
        $thesis->setCollectionTitle(Record::getCollectionTitle($record));
        $thesis->setContainerTitle(Record::getContainerTitle($record));
        $thesis->setDOI(Record::getDOI($record));
        $thesis->setEdition(Record::getEdition($record));
        $thesis->setEditor(Name::getEditor($record));
        $thesis->setGenre(self::getGenre($record));
        $thesis->setISBN(Record::getISBN($record));
        $thesis->setIssued(Date::getIssued($record));
        //$thesis->setMedium(Record::getMedium($record));
        $thesis->setNumberOfPages(Record::getNumberOfPages($record));
        $thesis->setOriginalTitle(Record::getOriginalTitle($record));
        $thesis->setPublisher(Record::getPublisher($record));
        $thesis->setTitle(Record::getTitle($record));
        $thesis->setTranslator(Name::getTranslator($record));
        $thesis->setURL(Record::getURL($record));
        $thesis->setVolume(Record::getVolume($record));
        $thesis->setType("thesis");
        return json_encode($thesis);
    }

    private static function getGenre(\File_MARC_Record $record)
    {
        /*
enthält 502 $a (nach dem 2. Komma) "diss", dann: Dissertation
WENN 502 $b = Dissertation, dann: Dissertation
enthält 502 $a (nach dem 2. Komma) "habil", dann: Habilitation
WENN 502 $b = Habilitationsschrift, dann: Habilitation
SONST: Abschlussarbeit
To enable screen reader support, press shortcut ⌘+Option+Z. To learn about keyboard shortcuts, press shortcut ⌘slash.
         */
        /** @var \File_MARC_Data_Field $field */
        $field = $record->getField("502");
        $diss = false;
        $habil = false;
        //$master = ;

        if (!empty($field->getSubfield("a"))) {
            $a = explode(",", $field->getSubfield("a")->getData());
            $diss = is_array($a) && array_key_exists(2, $a) && stripos($a[2], "diss") !== false;
            $habil = is_array($a) && array_key_exists(2, $a) && stripos($a[2], "habil") !== false;
        }

        if ($diss) {
            return "PhD thesis";
        } else {
            $b = $field->getSubfield("b");
            if (!empty($b) && stripos($b->getData(), "Dissertation") !== false) {
                return "PhD thesis";
            }
        }

        if ($habil) {
            return "habilitation thesis";
        } else {
            $b = $field->getSubfield("b");
            if (!empty($b) && stripos($b->getData(), "Habilitationsschrift") !== false) {
                return "habilitation thesis";
            }
        }

        return "master thesis";
    }
}