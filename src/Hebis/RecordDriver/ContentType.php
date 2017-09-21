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

namespace Hebis\RecordDriver;

use Hebis\Marc\Helper;
use Hebis\RecordDriver\SolrMarc;

class ContentType
{
    private static $physicalDescription = [
        "a" => [
            "a" => [
                //"xxx" => "article",
                "c" => "article",
                "cr" => "article",
                "t" => "article"
            ],
            "m" => [
                "xxx" => "book",
                "tu" => "hierarchy", //bei leader 19 = a
                "co" => "dvd",
                "c " => "cd",
                "c|" => "cd",
                "cr" => "hierarchy", //bei leader 19 = a
                "cu" => "ebook",
                "h" => "microfilm",
                "f" => "sensorimage",
                "o" => "hierarchy",
                "r" => "retro",
                "t" => "book",
            ],
            "s" => [
                "xxx" => "journal",
                "t" => "journal",
                //"tu" => "journal",
                "h" => "journal",
                "co" => "journal",
                "cocd" => "journal",
                "cr" => "electronic",
                "c " => "journal",
                "c|" => "journal",
                "f" => "sensorimage",

            ],
            "i" => [
                "cr" => "electronic",
                "t" => "book",
                "tu" => "hierarchy",
            ]
        ],
        "c" => [
            "m" => [
                "q" => "musicalscore"
            ],
            "s" => [
                "q" => "musicalscore"
            ]
        ],
        "e" => [
            "m" => [
                "a" => "map"
            ],
            "s" => [
                "a" => "map"
            ]
        ],
        "g" => [
            "m" => [
                "m" => "video",
                "v" => "hierarchy",
                "xxx" => "video"
            ],
            "s" => [
                "m" => "video",
                "xxx" => "video",
                "v" => "video"
            ]
        ],
        "i" => [
            "m" => [
                "co" => "audio",
                "c " => "audio",
                "c|" => "audio",
                "s" => "audio"
            ]
        ],
        "j" => [
            "m" => [
                "xxx" => "audio",
                "s" => "audio"
            ],
            "s" => [
                "co" => "audio",
                "c " => "audio",
                "c|" => "audio"
            ]
        ],
        "k" => [
            "m" => [
                "a" => "photo",
                "k" => "photo",
                "c" => "photo"
            ]
        ],
        "o" => [
            "m" => [
                "xxx" => "kit",
                "v" => "hierarchy",
                "o" => "hierarchy",
                "c" => "hierarchy",
            ],
            "i" => [
                "o" => "hierarchy",
            ],
        ],
        "r" => [
            "m" => [
                "xxx" => "physicalobject",
                "z" => "physicalobject",
            ]
        ],
        "t" => [
            "m" => [
                "t" => "manuscript",
                "xxx" => "manuscript"
            ]
        ]
    ];


    public static function getPhysicalDescription()
    {
        return self::$physicalDescription;
    }


    public static function getContentType(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $art = substr($marcRecord->getLeader(), 6, 1);
        $level = substr($marcRecord->getLeader(), 7, 1);
        $phys = " ";

        if (!empty($_007 = $marcRecord->getField('007'))) {
            $phys = substr($_007->getData(), 0, 1);
        }

        if ($phys === "c") {
            $phys = substr($_007->getData(), 0, 2);
        }


        /*
         * Exceptions for leader19 != a
         * In case of leader19 != a the relevant fields in physicalDescription table will be overwritten
        */

        $leader19 = substr($marcRecord->getLeader(), 19, 1);
        if ($leader19 != 'a') {
            self::$physicalDescription["a"]["m"]["tu"] = "book";
            self::$physicalDescription["a"]["i"]["tu"] = "book";
            self::$physicalDescription["g"]["m"]["v"] = "video";
            self::$physicalDescription["o"]["m"]["o"] = "kit";
            self::$physicalDescription["o"]["m"]["c"] = "kit";
        }

        // Exceptions for CD/DVD
        $_300_a = Helper::getSubFieldDataOfField($record, 300, 'a');
        switch ($phys) {
            case 'c':
                $phys_ = $phys . substr($marcRecord->getField('007')->getData(), 1, 1);
                /* $materialart["a"]["m"]["c "]: … wenn 338 $bvd oder wenn 300 $aDVD:  ="dvd"; … sonst: ="cd" */
                if ($phys_ == "c " || $phys_ == "c|") {
                    $_338_b = Helper::getSubFieldDataOfField($record, 338, 'b');
                    if ($_338_b == "vd" || strpos($_300_a, "DVD") !== false) {
                        $phys = "co";
                    } else {
                        $phys = "c ";
                    }
                }
                break;
            case " ":
                $phys = "xxx";
        }
        if ($art == "a" && ($level == "m" || $level == "i")) {
            if ($phys == "co") {
                if (strpos($_300_a, "DVD") === false && strpos($_300_a, "Blu-Ray") === false) {
                    $phys = "c ";
                }
            }
            if ($phys == "c " || $phys == "c|") {
                $_338_b = Helper::getSubFieldDataOfField($record, 338, 'b');
                if (strpos($_300_a, "DVD") !== false || strpos($_338_b, "vd") !== false) {
                    $phys = "co";
                }
            }
        }


        /* Falls in 856 $3 der Inhalt "Katalogkarte" vorhanden ist UND Art=a, Level=m und Phys=xxx, dann Phys = r. */
        if ($art == "a" && $level == "m" && $phys == "xxx") {
            $_856_3 = Helper::getSubFieldDataOfField($record, 856, '3');
            if (is_string($_856_3) && strpos($_856_3, "Katalogkarte") !== false) {
                $phys = "r";
            }
        }

        $className = isset(self::$physicalDescription[$art][$level][$phys]) ? self::$physicalDescription[$art][$level][$phys] : "";
        return $className;
    }

    /*
    public static function getContentType(SolrMarc $record)
    {

        $marcRecord = $record->getMarcRecord();

        $art = substr($marcRecord->getLeader(), 6, 1);
        $level = substr($marcRecord->getLeader(), 7, 1);
        $phys = " ";

        if (!empty($_007 = $marcRecord->getField('007'))) {
            $phys = substr($_007->getData(), 0, 1);
        }

        if ($phys === "c") {
            $phys = substr($_007->getData(), 0, 2);
        }

        if ($l19 = substr($marcRecord->getLeader(), 19, 1) == 'a') {
            $phys = substr($_007->getData(), 0, 2);
            if (($art == "a" || $art == "o") && ($level == "m" || $level == "i")) {
                if ($phys == "tu" || $phys == "cr" || $phys == "o") {
                    $phys .= "y";
                    return isset(self::$physicalDescription[$art][$level][$phys]) ? self::$physicalDescription[$art][$level][$phys] : "";
                }
            }
        }

        // Exceptions for CD/DVD
        $_300_a = Helper::getSubFieldDataOfField($record, 300, 'a');

        switch ($phys) {
            case 'c':
                $phys_ = $phys . substr($marcRecord->getField('007')->getData(), 1, 1);
                // $materialart["a"]["m"]["c "]: … wenn 338 $bvd oder wenn 300 $aDVD:  ="dvd"; … sonst: ="cd"
                if ($phys_ == "c " || $phys_ == "c|") {
                    $_338_b = Helper::getSubFieldDataOfField($record, 338, 'b');
                    if ($_338_b == "vd" || strpos($_300_a, "DVD") !== false) {
                        $phys = "co";
                    } else {
                        $phys = "c ";
                    }
                }
                break;
            case " ":
                $phys = "xxx";
        }
        if ($art == "a" && ($level == "m" || $level == "i")) {
            if ($phys == "co") {
                if (strpos($_300_a, "DVD") === false && strpos($_300_a, "Blu-Ray") === false) {
                    $phys = "c ";
                }
            }
            if ($phys == "c " || $phys == "c|") {
                $_338_b = Helper::getSubFieldDataOfField($record, 338, 'b');
                if (strpos($_300_a, "DVD") !== false || strpos($_338_b, "vd") !== false) {
                    $phys = "co";
                }
            }
        }

        //$_338_b = Helper::getSubFieldDataOfField($record, 338, 'b');
        //if ($_338_b === "vd") {
        //    $phys = "co";
        //}


        // Falls in 856 $3 der Inhalt "Katalogkarte" vorhanden ist UND Art=a, Level=m und Phys=xxx, dann Phys = r.
        if ($art == "a" && $level == "m" && $phys == "xxx") {
            $_856_3 = Helper::getSubFieldDataOfField($record, 856, '3');
            if (is_string($_856_3) && strpos($_856_3, "Katalogkarte") !== false) {
                $phys = "r";
            }
        }


        $className = isset(self::$physicalDescription[$art][$level][$phys]) ? self::$physicalDescription[$art][$level][$phys] : "";

        return $className;
    }
    */
}
