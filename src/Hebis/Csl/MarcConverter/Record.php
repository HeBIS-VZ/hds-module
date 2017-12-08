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

use Hebis\RecordDriver\ContentType;

class Record
{

    use SubfieldsTrait;

    /**
     * @param \File_MARC_Record $record
     * @return string|null
     */
    public static function getContainerTitle(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "490", "a");
    }


    public static function getDimensions(\File_MARC_Record $record)
    {
        $ret = "";
        $c = self::getSubfield($record, "300", "c");

        if (empty($c)) {
            $a = self::getSubfield($record, "300", "a");
            $b = self::getSubfield($record, "300", "b");

            $ret .= !empty($a) ? $a : "";
            $ret .= !empty($b) ? ", $b" : "";
            return $ret;
        }

        return $c;
    }

    public static function getEdition($record)
    {
        return self::getSubfield($record, "250", "a");
    }

    public static function getISSN($record)
    {
        return self::getSubfield($record, "490", "x");
    }


    public static function getPage($record)
    {
        $page = self::getSubfield($record, "773", "g");

        if (preg_match("/,?\s?S\.\s([0-9\-]+)$/", trim($page), $match)) {
            return $match[1];
        } else if (!empty($page) && strpos($page, ",") !== false) {
            $pos = strrpos($page, ",");
            return trim(substr($page, $pos));
        }
        return null;
    }

    public static function getPublisher($record)
    {
        return self::getSubfield($record, "264", "b");
    }

    public static function getPublisherPlace($record)
    {
        return self::getSubfield($record, "264", "a");
    }

    public static function getTitle($record)
    {
        $a = self::getSubfield($record, "245", "a");
        $b = self::getSubfield($record, "245", "b");

        return !empty($b) ? "$a : $b" : $a;
    }

    public static function getURL($record)
    {
        return self::getSubfield($record, "850", "u", function ($field) {
            /** @var \File_MARC_Data_Field $field */
            return ($field->getIndicator(1) === "4" && $field->getIndicator(2) === "0")
                || ($field->getIndicator(1) === " " && $field->getIndicator(2) === " ");
        });
    }

    public static function getISBN($record)
    {
        return self::getSubfield($record, "020", "a");
    }

    public static function getCollectionNumber(\File_MARC_Record $record)
    {
        $collectionNumber = null;
        $leader = $record->getLeader();
        if ($leader{19} === "a") {
            $collectionNumber = self::getSubfield($record, "490", "v", function ($field) {
                /** @var \File_MARC_Data_Field $field */
                return $field->getIndicator(1) == "1";
            });
        }

        if ($leader{19} === "c") {
            $collectionNumber = self::getSubfield($record, "245", "n");
        }
        return $collectionNumber;
    }

    public static function getCollectionTitle(\File_MARC_Record $record)
    {
        $collectionTitle = null;
        $leader = $record->getLeader();
        if ($leader{19} === "a") {
            $collectionTitle = self::getSubfield($record, "490", "a", function ($field) {
                /** @var \File_MARC_Data_Field $field */
                return $field->getIndicator(1) == "1";
            });
        }
        if ($leader{19} === "c") {
            $collectionTitle = self::getSubfield($record, "245", "a");
        }
        return $collectionTitle;
    }

    /**
     * returns MARC 300$a
     *
     * @param \File_MARC_Record $record
     * @return null|string
     */
    public static function getNumberOfPages(\File_MARC_Record $record)
    {
        $pages = self::getSubfield($record, "300", "a");

        if (preg_match("/[\s,-:;]?(\d+)(\s?S\.)$/", $pages, $match)) {
            return $match[1];
        }
        return $pages;
    }

    /**
     * return MARC 730$a
     * @param \File_MARC_Record $record
     * @return null|string
     */
    public static function getOriginalTitle(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "730", "a");
    }

    /**
     * returns MARC 490$v
     * @param \File_MARC_Record $record
     * @return null|string
     */
    public static function getVolume(\File_MARC_Record $record)
    {
        $volume = self::getSubfield($record, "490", "v");
        if (empty($volume))
        {
            /* Für Artikel ist das aus Volume 773g zu ziehen, da 490 für Aufsätze leer ist */
            if ((new ContentType())->getContentTypeFromMarcRecord($record) === "article") {
                $_773b = self::getSubfield($record, "773", "g");
                if (preg_match("/^(\d+).+H\.\s\d+/", $_773b, $match)) {
                    return $match[1];
                }
                return "";
            }
        }
        return $volume;
    }


    public static function getIssue(\File_MARC_Record $record)
    {
        //773 $b._-_$g
        $_773b = self::getSubfield($record, "773", "g");

        if (preg_match("/H\.\s(\d+)/", $_773b, $match)) {
            return $match[1];
        }
        return "";
    }

    public static function getURN(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "024", "a", function ($field) {
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(1) === "7" && $field->getSubfield('2')->getData() === "urn";
        });
    }


    public static function getDOI(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "024", "a", function ($field) {
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(1) === "7" && $field->getSubfield('2')->getData() === "doi";
        });
    }
}
