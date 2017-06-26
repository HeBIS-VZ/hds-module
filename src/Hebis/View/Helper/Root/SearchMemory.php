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

namespace Hebis\View\Helper\Root;


/**
 * Class SearchMemory
 * @package Hebis\View\Helper\Root
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SearchMemory extends \VuFind\View\Helper\Root\SearchMemory
{

    public function getLastSearchTerm($searchClassId, $shorten = false, $maxLength = 16)
    {
        $url = parse_url($this->memory->retrieveSearchOf($searchClassId));
        if (array_key_exists('query', $url)) {
            $query = $url['query'];
            if (preg_match("/lookfor\=([^&]*){1}/", $query, $match)) {
                $searchTerm = !empty($match) ? explode(" ", urldecode($match[1])) : null;

                if (count($searchTerm) > 1) {
                    $ret = "";
                    foreach ($searchTerm as $term) {
                        if (strlen($ret) + strlen($term) < $maxLength) {
                            $ret .= " " . $term;
                        } else {
                            $ret .= " " . substr($term, 0, $maxLength - 1 - strlen($ret)) . "…";
                        }
                    }
                    return trim($ret);
                } else {
                    if ($shorten && strlen($searchTerm[0] > $maxLength)) {
                        return substr($searchTerm[0], 0, $maxLength - 1) . "…";
                    }
                    return $searchTerm[0];
                }
            }
        }
        return "";
    }


    /**
     * If a previous search is recorded in the session, return a link to it;
     * otherwise, return a blank string.
     *
     * @param string $link   Text to use as body of link
     * @param string $prefix Text to place in front of link
     * @param string $suffix Text to place after link
     *
     * @return string
     */
    public function getLastSearchLinkOfSearchClassId($link, $searchClassId, $prefix = '', $suffix = '')
    {
        $last = $this->memory->retrieveSearchOf($searchClassId);
        if (!empty($last)) {
            $escaper = $this->getView()->plugin('escapeHtml');
            return $prefix . '<a href="' . $escaper($last) . '">' . $link . '</a>'
                . $suffix;
        }
        return '';
    }


}
