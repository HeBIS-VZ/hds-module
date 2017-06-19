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

namespace Hebis\ILS\Driver;

/**
 * Class PICACharsetMapping
 * @package Hebis\ILS\Driver
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class PICACharsetMapping
{

    /**
     * @var array
     */
    private static $charset = [];

    /**
     * initialize charset mapping
     */
    private static function init()
    {
        self::$charset[161] = "Ł";
        self::$charset[162] = "Ø";
        self::$charset[163] = "Đ";
        self::$charset[164] = "Þ";
        self::$charset[165] = "Æ";
        self::$charset[166] = "Œ";
        self::$charset[167] = "ʹ";
        self::$charset[168] = "·";
        self::$charset[169] = "♭";
        self::$charset[170] = "®";
        self::$charset[171] = "±";
        self::$charset[172] = "Ơ";
        self::$charset[173] = "Ư";
        self::$charset[174] = "ʾ";
        self::$charset[175] = "Å";
        self::$charset[176] = "ʿ";
        self::$charset[177] = "ł";
        self::$charset[178] = "ø";
        self::$charset[179] = "đ";
        self::$charset[180] = "þ";
        self::$charset[181] = "æ";
        self::$charset[182] = "œ";
        self::$charset[183] = "ʺ";
        self::$charset[184] = "ı";
        self::$charset[185] = "£";
        self::$charset[186] = "ð";
        self::$charset[187] = "α";
        self::$charset[188] = "ơ";
        self::$charset[189] = "ư";
        self::$charset[190] = "ß";
        self::$charset[191] = "å";
        self::$charset[192] = "Ĳ";
        self::$charset[193] = "Ä";
        self::$charset[194] = "Ö";
        self::$charset[195] = "Ü";
        self::$charset[196] = "Ɔ";
        self::$charset[197] = "Ǝ";
        self::$charset[198] = "≠";
        self::$charset[199] = "→";
        self::$charset[200] = "≤";
        self::$charset[201] = "∞";
        self::$charset[202] = "∫";
        self::$charset[203] = "×";
        self::$charset[204] = "§";
        self::$charset[205] = "√";
        self::$charset[206] = "⇔";
        self::$charset[207] = "≥";
        self::$charset[208] = "ĳ";
        self::$charset[209] = "ä";
        self::$charset[210] = "ö";
        self::$charset[211] = "ü";
        self::$charset[212] = "ɔ";
        self::$charset[213] = "ɘ";
        self::$charset[214] = "¿";
        self::$charset[215] = "¡";
        self::$charset[216] = "β";
        self::$charset[217] = "";
        self::$charset[218] = "γ";
        self::$charset[219] = "π";

        // grave
        self::$charset[0xe141] = "À";
        self::$charset[0xe145] = "È";
        self::$charset[0xe149] = "Ì";
        self::$charset[0xe14f] = "Ò";
        self::$charset[0xe155] = "Ù";
        self::$charset[0xe161] = "à";
        self::$charset[0xe165] = "è";
        self::$charset[0xe169] = "ì";
        self::$charset[0xe16f] = "ò";
        self::$charset[0xe175] = "ù";

        // acute

        self::$charset[0xe2] = "'";
        self::$charset[0xe241] = "Á";
        self::$charset[0xe245] = "É";
        self::$charset[0xe249] = "Í";
        self::$charset[0xe24f] = "Ó";
        self::$charset[0xe255] = "Ú";
        self::$charset[0xe261] = "á";
        self::$charset[0xe265] = "é";
        self::$charset[0xe269] = "í";
        self::$charset[0xe26f] = "ó";
        self::$charset[0xe275] = "ú";

        // circumflex
        self::$charset[0xe3] = "^";
        self::$charset[0xe341] = "Â";
        self::$charset[0xe345] = "Ê";
        self::$charset[0xe349] = "Î";
        self::$charset[0xe34f] = "Ô";
        self::$charset[0xe355] = "Û";
        self::$charset[0xe361] = "â";
        self::$charset[0xe365] = "ê";
        self::$charset[0xe369] = "î";
        self::$charset[0xe36f] = "ô";
        self::$charset[0xe375] = "û";

        // tilde
        /* ~A => Ã */

        self::$charset[0xe4] = "~";
        self::$charset[0xe441] = "Ã";
        self::$charset[0xe445] = "Ẽ";
        self::$charset[0xe449] = "Ĩ";
        self::$charset[0xe44f] = "Õ";
        self::$charset[0xe455] = "Ũ";
        self::$charset[0xe461] = "ã";
        self::$charset[0xe465] = "ẽ";
        self::$charset[0xe469] = "ĩ";
        self::$charset[0xe46f] = "õ";
        self::$charset[0xe475] = "ũ";

        // andere

        self::$charset[0xf043] = "Ç";
        self::$charset[0xe849] = "Ï";
        self::$charset[0xe845] = "Ë";
        self::$charset[0xe44e] = "Ñ";
        self::$charset[0xe259] = "Ý";
        self::$charset[0xf063] = "ç";
        self::$charset[0xe869] = "ï";
        self::$charset[0xe865] = "ë";
        self::$charset[0xe46e] = "ñ";
        self::$charset[0xe279] = "ý";
    }


    /**
     * converts PICA charset string to an UTF8 string
     *
     * @param string $string
     * @return string UTF8 string
     */
    public static function toUTF8($string)
    {

        if (empty(self::$charset)) {
            static::init();
        }

        $new = "";
        for ($i = 0; $i < strlen($string); $i++) {
            // single-Byte
            $b = ord($string[$i]);
            if ($b >= 160) {
                // double-Byte
                if ($b > 0xe1) {
                    $currentChar = ($b << 8) + ord($string[$i + 1]);
                    $new .= self::$charset[$currentChar];
                    $i++;
                } else {
                    $new .= self::$charset[$b];
                }
            } else {
                $new .= $string[$i];
            }
        }

        return (string)$new;
    }
}
