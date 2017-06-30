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

namespace Hebis\RecordDriver;

class MediaTypes
{
    /**
     * @var array;
     */
    private static $mediaTypes = [];

    public static function getMediaTypesMap()
    {
        //just initialize once
        if (empty(self::$mediaTypes)) {
            self::initMediaTypes();
        }

        return self::$mediaTypes;
    }

    private static function initMediaTypes()
    {
        /* css classes available in blueprint theme
        .manuscript
        .ebook
        .book
        .journal
        .newspaper
        .software
        .physicalobject
        .cd
        .dvd
        .electronic
        .map
        .globe
        .slide
        .microfilm
        .photo
        .video
        .kit
        .musicalscore
        .sensorimage
        .audio
        */

        // Im Moment ausgewertete Materialart
        self::$mediaTypes = array(array(array()));
        self::$mediaTypes["a"]["m"]["xxx"] = "book";
        self::$mediaTypes["a"]["m"]["co"] = "dvd";
        self::$mediaTypes["a"]["m"]["cocd"] = "cd";
        self::$mediaTypes["a"]["m"]["c "] = "cd";
        self::$mediaTypes["a"]["m"]["cr"] = "ebook";
        self::$mediaTypes["a"]["m"]["cu"] = "ebook";
        self::$mediaTypes["a"]["m"]["h"] = "microfilm";
        self::$mediaTypes["a"]["m"]["f"] = "braille";
        self::$mediaTypes["a"]["m"]["o"] = "kit";
        self::$mediaTypes["a"]["s"]["xxx"] = "journal";
        self::$mediaTypes["a"]["s"]["t"] = "journal";
        self::$mediaTypes["a"]["s"]["h"] = "journal";
        self::$mediaTypes["a"]["s"]["co"] = "journal";
        self::$mediaTypes["a"]["s"]["cocd"] = "journal";
        self::$mediaTypes["a"]["s"]["cr"] = "electronic";
        self::$mediaTypes["a"]["s"]["f"] = "braille";
        self::$mediaTypes["c"]["m"]["q"] = "musicalscore";
        self::$mediaTypes["c"]["s"]["q"] = "musicalscore";
        self::$mediaTypes["e"]["m"]["a"] = "map";
        self::$mediaTypes["e"]["s"]["a"] = "map";
        self::$mediaTypes["g"]["m"]["m"] = "video";
        self::$mediaTypes["g"]["m"]["xxx"] = "video";
        self::$mediaTypes["g"]["s"]["m"] = "video";
        self::$mediaTypes["g"]["s"]["xxx"] = "video";
        self::$mediaTypes["i"]["m"]["s"] = "audio";
        self::$mediaTypes["i"]["m"]["cocd"] = "cd";
        self::$mediaTypes["j"]["m"]["xxx"] = "audio";
        self::$mediaTypes["j"]["m"]["s"] = "audio";
        self::$mediaTypes["j"]["m"]["cocd"] = "audio";
        self::$mediaTypes["j"]["s"]["co"] = "audio";
        self::$mediaTypes["j"]["s"]["s"] = "audio";
        self::$mediaTypes["k"]["m"]["a"] = "photo";
        self::$mediaTypes["k"]["m"]["k"] = "photo";
        self::$mediaTypes["k"]["m"]["cr"] = "photo";
        self::$mediaTypes["o"]["m"]["xxx"] = "kit";
        self::$mediaTypes["o"]["m"]["o"] = "kit";
        self::$mediaTypes["r"]["m"]["xxx"] = "physicalobject";
        self::$mediaTypes["r"]["m"]["z"] = "physicalobject";
        self::$mediaTypes["t"]["m"]["xxx"] = "manuscript";
    }
}