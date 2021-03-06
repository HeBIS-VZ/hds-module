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

namespace Hebis\View\Helper\Record\SingleRecord;

use Hebis\RecordDriver\SolrMarc;
use Hebis\Marc\Helper;

/**
 * Class SingleRecordOWPersonalName
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Claudia Grote <grote@hebis.uni-frankfurt.de>
 */
class SingleRecordOWPersonalName extends SingleRecordPersonalName
{

    public function __invoke(SolrMarc $record, $test = true)
    {
        $arr = [];
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $_880_700_ = array();
        $_880__ = $marcRecord->getFields('880');

        foreach ($_880__ as $_880) {
            $_880_6 = empty($_880) ? "" : Helper::getSubFieldDataOfGivenField($_880, '6');
            if (strncmp("100", $_880_6, 3) == 0) {
                $_880_100 = $_880;
                $aut = $this->getFieldContents($_880_100);
                $autLink = $this->getFieldContents($_880_100, false);
            } else {
                if (strncmp("700", $_880_6, 3) == 0) {
                    $_880_700_[] = $_880;
                }
            }
        }

        if (!empty($aut)) {
            if (!$test) {
                $wiki = $wikiLink = "";
                $gnd = $this->getGnd($_880_100);
                if (!empty($gnd)) {
                    $wiki = '<div class="hidden" id="gnd_' . $gnd . '"><div class="popover-heading"></div><div class="popover-body"></div></div>';
                    $wikiLink = '<sup><a role="button" class="wiki-gnd-popover" id="wiki-' . $gnd . '" data-id="' . $gnd . '" data-container="body" data-popover-content="#gnd_' . $gnd . '"><span class="hds-icon-wikipedia-w"><!-- --></span></a></sup>';
                }
                $arr[] = $this->addLink($record, $aut, $autLink) . $wikiLink . $wiki;
            } else {
                $arr[] = $aut;
            }
        }

        foreach ($_880_700_ as $_880_700) {
            if (empty($_880_700->getSubfields('e'))) {
                $this->appendMissingESubfields($_880_700);
            }

            $addedEntryPN = $this->getFieldContents($_880_700);
            $addedEntryPNLink = $this->getFieldContents($_880_700, false);

            if (!empty($addedEntryPN)) {
                if (!$test) {
                    $wiki = $wikiLink = "";
                    $gnd = $this->getGnd($_880_700);
                    if (!empty($gnd)) {
                        $wiki = '<div class="hidden" id="gnd_' . $gnd . '"><div class="popover-heading"></div><div class="popover-body"></div></div>';
                        $wikiLink = '<sup><a role="button" class="wiki-gnd-popover" id="wiki-' . $gnd . '" data-id="' . $gnd . '" data-container="body" data-popover-content="#gnd_' . $gnd . '"><span class="hds-icon-wikipedia-w"><!-- --></span></a></sup>';
                    }
                    $arr[] = $this->addLink($record, $addedEntryPN, $addedEntryPNLink) . $wikiLink . $wiki;
                } else {
                    $arr[] = $addedEntryPN;
                }
            }
        }

        return implode("; ", $arr);
    }
}
