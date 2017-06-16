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
 * Class PICAUtils PICAUtils. Utility functions needed to work with PICA
 * @package Hebis\ILS\Driver
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class PICAUtils
{

    /**
     * Normalize raw PPN (Pica Production Number) to a normalized PPN like from OPAC
     *
     * converts a raw e.g. from database PPN w/o check digit to a
     * left-zero-padded-with-check-digit PPN
     * @param string $rawppn
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
     * @return string    normalized PPN like from OPAC
     */

    public static function picaPPNPZ($rawppn)
    {

        // pad the raw ppn with left zero
        $ppn = sprintf("%08s", $rawppn);
        // weigh every digit from left (2,3,4,5,6,7,8,9)
        for ($i = 0, $pz = 0; $i < 8; $i++) {
            $pz += ($ppn{$i} * ($i + 2));
        }
        // modulo 11
        $pz = $pz % 11;
        // 0 => check digit 'X', 10 => checkdigit '0',
        // else calculated digit from 1-9
        if ($pz == 0 || $pz == 10) {
            return "HEB" . ($pz == 10 ? $ppn . 'X' : $ppn . '0');
        } else {
            return "HEB" . $ppn . $pz;
        }
    }
}
