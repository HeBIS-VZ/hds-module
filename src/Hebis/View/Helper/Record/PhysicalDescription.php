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

namespace Hebis\View\Helper\Record;
use Hebis\RecordDriver\SolrMarc;


/**
 * Class PhysicalDescription
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class PhysicalDescription extends AbstractRecordViewHelper
{


    private $physicalDescription;
    
    public function initMap()
    {
        $this->physicalDescription = [];
        $this->physicalDescription["a"]["m"]["xxx"]="book";
        $this->physicalDescription["a"]["m"]["co"]="dvd";
        $this->physicalDescription["a"]["m"]["cocd"]="cd";
        $this->physicalDescription["a"]["m"]["c "]="cd";
        $this->physicalDescription["a"]["m"]["cr"]="ebook";
        $this->physicalDescription["a"]["m"]["cu"]="ebook";
        $this->physicalDescription["a"]["m"]["h"]="microfilm";
        $this->physicalDescription["a"]["m"]["f"]="sensorimage";
        $this->physicalDescription["a"]["m"]["o"]="kit";
        $this->physicalDescription["a"]["s"]["xxx"]="journal";
        $this->physicalDescription["a"]["s"]["t"]="journal";
        $this->physicalDescription["a"]["s"]["h"]="journal";
        $this->physicalDescription["a"]["s"]["co"]="journal";
        $this->physicalDescription["a"]["s"]["cocd"]="journal";
        $this->physicalDescription["a"]["s"]["cr"]="electronic";
        $this->physicalDescription["a"]["s"]["f"]="sensorimage";
        $this->physicalDescription["c"]["m"]["q"]="musicalscore";
        $this->physicalDescription["c"]["s"]["q"]="musicalscore";
        $this->physicalDescription["e"]["m"]["a"]="map";
        $this->physicalDescription["e"]["s"]["a"]="map";
        $this->physicalDescription["g"]["m"]["m"]="video";
        $this->physicalDescription["g"]["m"]["xxx"]="video";
        $this->physicalDescription["g"]["s"]["m"]="video";
        $this->physicalDescription["g"]["s"]["xxx"]="video";
        $this->physicalDescription["g"]["m"]["v"]="video";
        $this->physicalDescription["g"]["s"]["v"]="video";
        $this->physicalDescription["i"]["m"]["s"]="audio";
        $this->physicalDescription["i"]["m"]["cocd"]="cd";
        $this->physicalDescription["j"]["m"]["xxx"]="audio";
        $this->physicalDescription["j"]["m"]["s"]="audio";
        $this->physicalDescription["j"]["m"]["cocd"]="audio";
        $this->physicalDescription["j"]["s"]["co"]="audio";
        $this->physicalDescription["j"]["s"]["s"]="audio";
        $this->physicalDescription["k"]["m"]["a"]="photo";
        $this->physicalDescription["k"]["m"]["k"]="photo";
        $this->physicalDescription["k"]["m"]["cr"]="photo";
        $this->physicalDescription["o"]["m"]["xxx"]="kit";
        $this->physicalDescription["o"]["m"]["o"]="kit";
        $this->physicalDescription["r"]["m"]["xxx"]="physicalobject";
        $this->physicalDescription["r"]["m"]["z"]="physicalobject";
        $this->physicalDescription["t"]["m"]["xxx"]="manuscript";
    }


    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        $this->initMap();
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $x = substr($marcRecord->getLeader(), 6, 1);
        $y = substr($marcRecord->getLeader(), 7, 1);
        $z = " ";

        if (!empty($_007 = $marcRecord->getField('007'))) {
            $z = substr($_007->getData(), 0, 1);
        }

        // Exceptions for CD/DVD
        $_300_a = $this->getSubFieldDataOfField($record, 300, 'a');

        switch ($z) {
            case 'c':
                $z .= substr($marcRecord->getField('007')->getData(), 1, 1);
                /* $materialart["a"]["m"]["c "]: … wenn 338 $bvd oder wenn 300 $aDVD:  ="dvd"; … sonst: ="cd" */
                if ($z == "c ") {
                    $_338_b = $this->getSubFieldDataOfField($record, 338, 'b');
                    if ($_338_b == "vd" || strpos($_300_a, "DVD") !== false) {
                        $z = "co";
                    }
                }
                break;
            case " ":
                $z = "xxx";
        }
        if ($x == "a" && $y == "m") {
            if ($z == "co") {
                if (strpos($_300_a, "DVD") === false) {
                    $z = "c ";
                }
            }
            if ($z == "c ") {
                if (strpos($_300_a, "DVD") !== false) {
                    $z = "co";
                }
            }
        }

        $_338_b = $this->getSubFieldDataOfField($record, 338, 'b');
        if ($_338_b === "vd") {
            $z = "co";
        }

        $className = isset($this->physicalDescription[$x][$y][$z]) ? $this->physicalDescription[$x][$y][$z] : "";

        return $className;
    }
}