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

namespace Hebis\Search;


class Memory extends \VuFind\Search\Memory
{

    /**
     * Store the last accessed search URL in the session for future reference.
     *
     * @param $searchClassId
     * @param string $url URL to remember
     * @return void
     */
    public function rememberLastSearchOf($searchClassId, $url)
    {
        // Do nothing if disabled.
        if (!$this->active) {
            return;
        }

        // Only remember URL if string is non-empty... otherwise clear the memory.
        if (strlen(trim($url)) > 0) {
            $property = 'last' . $searchClassId;
            $this->session->{$property} = $url;
        } else {
            $this->forgetSearchOf($searchClassId);
        }
    }

    public function forgetSearchOf($searchClassId)
    {
        $property = 'last' . $searchClassId;
        unset($this->session->{$property});
    }

    /**
     * Retrieve last accessed search URL, if available.  Returns null if no URL
     * is available.
     *
     * @param $searchClassId
     * @return null|string
     */
    public function retrieveSearchOf($searchClassId)
    {
        $property = 'last' . $searchClassId;
        return isset($this->session->{$property}) ? $this->session->{$property} : null;
    }

}