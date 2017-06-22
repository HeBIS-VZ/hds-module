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

/**
 * //TODO: insert Class Description
 * Class ProxyURLBuilder
 * @package Hebis\RecordDriver
 */
class ProxyUrlBuilder
{

    /**
     * @var bool
     */
    private $withProxy = false;

    /**
     * @var string
     */
    private $proxy = "";

    /**
     * @var bool
     */
    private $restricted = true;

    /**
     * @var bool
     */
    private $encoded = true;

    /**
     *
     */
    public function __construct()
    {

        global $configArray;

        // Define Guest-Access
        //ignorier ich erst mal
        // $this->isrestricted = UserAccount::isUserRestricted();
        $this->restricted = false;
        // Proxy falls eingestellt
        if (isset($configArray['hproxy']['host'])) {
            $this->withProxy = true;
            $this->proxy = $configArray['hproxy']['host'];
        }

        // not encoded falls eingeschaltet
        if (isset($configArray['hproxy']['urlencode']) and ($configArray['hproxy']['urlencode'] === "0")) {
            $this->encoded = false;
        }

    }

    /**
     *
     */
    public function hasProxy()
    {
        return $this->withProxy;
    }

    /**
     *
     */
    public function isRestricted()
    {
        return $this->restricted;
    }


    /**
     *
     * @param string $url
     * @return string
     */
    public function addProxy($url)
    {
        if ($this->encoded) {

            return $this->proxy . urlencode($url);
        }

        return $this->proxy . $url;
    }

    /**
     *
     * @param string $url
     * @return mixed|string
     */
    public function removeProxy($url)
    {
        if ($this->encoded) {

            return urldecode(str_replace($this->proxy, "", $url));
        }

        return str_replace($this->proxy, "", $url);
    }
}

