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


use Hebis\Csl\Model\Layout\CslDate;
use Zend\Stdlib\JsonSerializable;

class Date implements CslDate, JsonSerializable
{
    use JsonSerializeTrait;

    private $dateParts;

    private $literal;

    private $circa;

    private $season;

    private $raw;

    /**
     * @param mixed $dateParts
     */
    public function setDateParts($dateParts)
    {
        $this->dateParts = $dateParts;
    }

    /**
     * @param mixed $literal
     */
    public function setLiteral($literal)
    {
        $this->literal = $literal;
    }

    /**
     * @param mixed $circa
     */
    public function setCirca($circa)
    {
        $this->circa = $circa;
    }

    /**
     * @param mixed $season
     */
    public function setSeason($season)
    {
        $this->season = $season;
    }

    /**
     * @param mixed $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }


    /**
     * array with order year, month, day
     * e.g.: [[2012],[6],[17]]
     *
     * @return array
     */
    public function getDateParts()
    {
        return $this->dateParts;
    }

    /**
     * @return string
     */
    public function getLiteral()
    {
        return $this->literal;
    }

    /**
     * @return string
     */
    public function getCirca()
    {
        return $this->circa;
    }

    /**
     * @return string
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }
}