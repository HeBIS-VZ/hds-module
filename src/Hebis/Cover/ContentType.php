<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 06.01.17
 * Time: 15:47
 */

namespace Hebis\Cover;


use Hebis\Marc\Helper;
use Hebis\RecordDriver\SolrMarc;

class ContentType
{
    private static $physicalDescription = [
        "a" => [
            "a" => [
                "xxx" => "article",
                "cr"  => "article"
            ],
            "m" => [
                "xxx" => "book",
                "co"  => "dvd",
                "cocd"=> "cd",
                "c "  => "cd",
                "cr"  => "ebook",
                "cu"  => "ebook",
                "h"   => "microfilm",
                "f"   => "sensorimage",
                "o"   => "kit",
                "r"   => "retro"
            ],
            "s" => [
                "xxx" => "journal",
                "t"   => "journal",
                "h"   => "journal",
                "co"  => "journal",
                "cocd"=> "journal",
                "cr"  => "electronic",
                "f"   => "sensorimage"
            ]
        ],
        "c" => [
            "m" => [
                "q"   => "musicalscore"
            ],
            "s" => [
                "q"   => "musicalscore"
            ]
        ],
        "e" => [
            "m" => [
                "a"   => "map"
            ],
            "s" => [
                "a"   => "map"
            ]
        ],
        "g" => [
            "m" => [
                "m"   => "video",
                "v"   => "video",
                "xxx" => "video"
            ],
            "s" => [
                "m"   => "video",
                "xxx" => "video",
                "v"   => "video"
            ]
        ],
        "i" => [
            "m" => [
                "s"   => "audio",
                "cocd"=> "cd"
            ]
        ],
        "j" => [
            "m" => [
                "xxx" => "audio",
                "s"   => "audio",
                "cocd"=> "audio"
            ],
            "s" => [
                "co"  => "audio",
                "s"   => "audio"
            ]
        ],
        "k" => [
            "m" => [
                "a"   => "photo",
                "k"   => "photo",
                "cr"  => "photo"
            ]
        ],
        "o" => [
            "m" => [
                "xxx" => "kit",
                "o"   => "kit",
            ]
        ],
        "r" => [
            "m" => [
                "xxx" => "physicalobject",
                "z"   => "physicalobject",
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
        if ($x == "a" && $y == "m") {
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