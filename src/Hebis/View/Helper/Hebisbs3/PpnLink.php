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

namespace Hebis\View\Helper\Hebisbs3;

use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use Zend\Validator\Uri;

/**
 * Class PpnLink
 * @package Hebis\View\Helper\Hebisbs3
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class PpnLink extends AbstractRecordViewHelper
{

    public function __invoke()
    {
        return $this;
    }

    public function getTransLink($transKey, $ppn, $newWindow = false)
    {
        return $this->getLink($this->getView()->transEsc($transKey), $ppn, $newWindow);
    }

    public function getLink($linkText, $ppn, $params = [], $newWindow = false)
    {
        if (!preg_match("/^HEB[\d]+/", $ppn)) {
            $ppn = "HEB" . $ppn;
        }

        $searchParams = array_merge([
            "lookfor" => "id:" . $ppn
        ], $params);

        return $this->generateSearchLink($linkText, $searchParams, $newWindow);
    }

    public function getRecordLink($linkText, $ppn)
    {
        if (!preg_match("/^HEB[\d]+/", $ppn)) {
            $ppn = "HEB" . $ppn;
        }

        return '<a href="' . $this->getView()->url('record') . $ppn . '">' . $linkText . '</a>';
    }
}