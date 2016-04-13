<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.16
 * Time: 10:27
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