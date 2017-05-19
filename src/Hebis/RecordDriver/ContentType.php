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
                "xxx" => "article",
                "cr" => "article"
            ],
            "m" => [
                "xxx" => "book",
                "xxxy" => "hierarchy", //bei leader 19 = a
                "co" => "dvd",
                "cocd" => "cd",
                "c " => "cd",
                "cr" => "ebook",
                "cry" => "hierarchy", //bei leader 19 = a
                "cu" => "ebook",
                "h" => "microfilm",
                "f" => "sensorimage",
                "o" => "kit",
                "r" => "retro"
            ],
            "s" => [
                "xxx" => "journal",
                "t" => "journal",
                "h" => "journal",
                "co" => "journal",
                "cocd" => "journal",
                "cr" => "electronic",
                "c " => "journal",
                "f" => "sensorimage"
            ],
            "i" => [
                "cr" => "electronic",
                "xxxy" => "hierarchy" //bei leader 19 = a
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
                "v" => "video",
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
                "s" => "audio",
                "cocd" => "cd"
            ]
        ],
        "j" => [
            "m" => [
                "xxx" => "audio",
                "s" => "audio",
                "cocd" => "audio"
            ],
            "s" => [
                "co" => "audio",
                "c " => "audio"
            ]
        ],
        "k" => [
            "m" => [
                "a" => "photo",
                "k" => "photo",
                "cr" => "photo"
            ]
        ],
        "o" => [
            "m" => [
                "xxx" => "kit",
                "o" => "kit",
            ]
        ],
        "r" => [
            "m" => [
                "xxx" => "physicalobject",
                "z" => "physicalobject",
            ]
        ],
        "t" => [
            "m" => [
                "xxx" => "manusscript"
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

        $x = substr($marcRecord->getLeader(), 6, 1);
        $y = substr($marcRecord->getLeader(), 7, 1);
        $z = " ";

        if (!empty($_007 = $marcRecord->getField('007'))) {
            $z = substr($_007->getData(), 0, 1);
        }

        // Exceptions for CD/DVD
        $_300_a = Helper::getSubFieldDataOfField($record, 300, 'a');

        switch ($z) {
            case 'c':
                $z .= substr($marcRecord->getField('007')->getData(), 1, 1);
                /* $materialart["a"]["m"]["c "]: … wenn 338 $bvd oder wenn 300 $aDVD:  ="dvd"; … sonst: ="cd" */
                if ($z == "c ") {
                    $_338_b = Helper::getSubFieldDataOfField($record, 338, 'b');
                    if ($_338_b == "vd" || strpos($_300_a, "DVD") !== false) {
                        $z = "co";
                    }
                }
                break;
            case " ":
                $z = "xxx";
        }
        if ($x == "a" && ($y == "m" || $y == "i")) {
            if ($z == "co") {
                if (strpos($_300_a, "DVD") === false && strpos($_300_a, "Blu-Ray") === false) {
                    $z = "c ";
                }
            }
            if ($z == "c ") {
                if (strpos($_300_a, "DVD") !== false) {
                    $z = "co";
                }
            }
            if ($z == "cr") {
                if (strpos($marcRecord->getLeader(), 19, 1) == 'a') {
                    $z = "cry";
                }
            }
            if ($z == "xxx") {
                if (strpos($marcRecord->getLeader(), 19, 1) == 'a') {
                    $z = "xxxy";
                }
            }
        }

        $_338_b = Helper::getSubFieldDataOfField($record, 338, 'b');
        if ($_338_b === "vd") {
            $z = "co";
        }

        /* Falls in 856 $3 der Inhalt "Katalogkarte" vorhanden ist UND Art=a, Level=m und Phys=xxx, dann Phys = r. */
        if ($x == "a" && $y == "m" && $z == "xxx") {
            $_856_3 = Helper::getSubFieldDataOfField($record, 856, '3');
            if (is_string($_856_3) && strpos($_856_3, "Katalogkarte") !== false) {
                $z = "r";
            }
        }


        $className = isset(self::$physicalDescription[$x][$y][$z]) ? self::$physicalDescription[$x][$y][$z] : "";

        return $className;
    }
}