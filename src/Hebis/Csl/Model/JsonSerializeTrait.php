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

namespace Hebis\Csl\Model;

use Hebis\Csl\Helper\Variables;

trait JsonSerializeTrait
{

    protected $jsonClass = null;

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        if (empty($this->jsonClass)) {
            $jsonFormat = new \stdClass();
            $reflClass = new \ReflectionClass(__CLASS__);
            $methods = $reflClass->getMethods();


            foreach ($methods as $method) {
                $name = $method->getName();
                if (substr($name, 0, 3) === "get" && $name !== "getPropertyName") {
                    $val = $method->invoke($this);
                    $property = $this->getPropertyName(substr($name, 3));
                    $jsonFormat->{$property} = $val;
                }
            }
            $this->jsonClass = $jsonFormat;
        }
        return $this->jsonClass;
    }


    private function getPropertyName($string)
    {
        if (in_array($string, ['DOI', 'ISBN', 'ISSN', 'URL'])) {
            return $string;
        }

        $name = lcfirst($string);
        $matches = [];

        if (preg_match_all("/[A-Z]/", $name, $matches, PREG_OFFSET_CAPTURE)) {
            $matches = $matches[0];
            $ret = "";
            $start = 0;
            array_walk($matches, function ($match) use ($name, &$start, &$ret) {
                $len = $match[1] - $start;
                $ret .= strtolower(substr($name, $start, $len)) . "-";
                $start = $match[1];
            });
            $match = array_pop($matches);
            $ret .= strtolower(substr($name, $match[1]));
            return $ret;
        }
        return $name;
    }
}
