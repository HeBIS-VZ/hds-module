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

namespace Hebis\Csl\Model\Layout;

/**
 * Interface CslName
 * Western names frequently contain one or more name particles (e.g. “de” in the Dutch name “W. de Koning”). These name
 * particles can be either kept or dropped when only the surname is shown: these two types are referred to as
 * non-dropping and dropping particles, respectively. A single name can contain particles of both types (with
 * non-dropping particles always following dropping particles). For example, “W. de Koning” and the French name
 * “Jean de La Fontaine” can be deconstructed into:
 *
 * <pre>
 * {
 *  "author": [
 *      {
 *          "given": "W.",
 *          "non-dropping-particle": "de",
 *          "family": "Koning"
 *      },
 *      {
 *          "given": "Jean",
 *          "dropping-particle": "de",
 *          "non-dropping-particle": "La",
 *          "family": "Fontaine"
 *      }
 *  ]
 * }
 * </pre>
 *
 * When just the surname is shown, only the non-dropping-particle is kept: “De Koning” and “La Fontaine”.
 *
 * @package Hebis\Csl\Model\Layout
 */
interface CslName
{
    /**
     * Given name
     * @return string
     */
    public function getGiven();

    /**
     * e.g. the “de“ in “Jean de La Fontaine“
     * @return string
     */
    public function getDroppingParticle();

    /**
     * e.g. “La“ in “Jean de La Fontaine“
     * @return string
     */
    public function getNonDroppingParticle();

    /**
     * Family name
     * @return string
     */
    public function getFamily();

    /**
     * e.g. “II“ in “Elisabeth II“ or “XVI“ in “Benedict XVI“
     * @return string
     */
    public function getSuffix();

}
