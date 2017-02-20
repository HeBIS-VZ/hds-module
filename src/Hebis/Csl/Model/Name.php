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


use Hebis\Csl\Model\Layout\CslName;
use stdClass;
use Zend\Stdlib\JsonSerializable;

class Name implements CslName, JsonSerializable
{

    use JsonSerializeTrait;

    private $given;

    private $droppingParticle;

    private $nonDroppingParticle;

    private $family;

    private $suffix;

    private $jsonFormat;

    /**
     * @return mixed
     */
    public function getGiven()
    {
        return $this->given;
    }

    /**
     * @param mixed $given
     */
    public function setGiven($given)
    {
        $this->given = $given;
    }

    /**
     * @return mixed
     */
    public function getDroppingParticle()
    {
        return $this->droppingParticle;
    }

    /**
     * @param mixed $droppingParticle
     */
    public function setDroppingParticle($droppingParticle)
    {
        $this->droppingParticle = $droppingParticle;
    }

    /**
     * @return mixed
     */
    public function getNonDroppingParticle()
    {
        return $this->nonDroppingParticle;
    }

    /**
     * @param mixed $nonDroppingParticle
     */
    public function setNonDroppingParticle($nonDroppingParticle)
    {
        $this->nonDroppingParticle = $nonDroppingParticle;
    }

    /**
     * @return mixed
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param mixed $family
     */
    public function setFamily($family)
    {
        $this->family = $family;
    }

    /**
     * @return mixed
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param mixed $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }


}