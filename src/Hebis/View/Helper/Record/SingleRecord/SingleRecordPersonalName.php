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
use Hebis\View\Helper\Record\ResultList\ResultListPersonalName;


/**
 * Class SingleRecordPersonalName
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordPersonalName extends ResultListPersonalName
{

    public function __invoke(SolrMarc $record, $test = true)
    {
        $arr = [];
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $field100 = $marcRecord->getField('100');

        $aut = $this->getFieldContents($field100);

        if (!empty($aut)) {
            if (!$test) {
                $wiki = $wikiLink = "";
                $gnd = $this->getGnd($field100);
                if (!empty($gnd)) {
                    $wiki = '<div class="hidden" id="gnd_' . $gnd . '"><div class="popover-heading"></div><div class="popover-body"></div></div>';
                    $wikiLink = '<sup><a role="button" class="wiki-gnd-popover" id="wiki-' . $gnd . '" data-id="' . $gnd . '" data-container="body" data-popover-content="#gnd_' . $gnd . '"><span class="hds-icon-wikipedia-w"><!-- --></span></a></sup>';
                }

                $arr[] = $this->addLink($record, $aut) . $wikiLink . $wiki;
            } else {
                $arr[] = $aut;
            }

        }

        $f700_ = $marcRecord->getFields(700);
        $filteredFields = $this->filterByIndicator($f700_, 2, " ");

        foreach ($filteredFields as $field) {
            if (empty($field->getSubfields('e'))) {
                $this->appendMissingESubfields($field);
            }
            $addedEntryPN = $this->getFieldContents($field);
            if (!empty($addedEntryPN)) {
                if (!$test) {
                    $wiki = $wikiLink = "";
                    $gnd = $this->getGnd($field);
                    if (!empty($gnd)) {
                        $wiki = '<div class="hidden" id="gnd_' . $gnd . '"><div class="popover-heading"></div><div class="popover-body"></div></div>';
                        $wikiLink = '<sup><a role="button" class="wiki-gnd-popover" id="wiki-' . $gnd . '" data-id="' . $gnd . '" data-container="body" data-popover-content="#gnd_' . $gnd . '"><span class="hds-icon-wikipedia-w"><!-- --></span></a></sup>';
                    }
                    $arr[] = $this->addLink($record, $addedEntryPN) . $wikiLink . $wiki;
                } else {
                    $arr[] = $addedEntryPN;
                }
            }
        }

        return implode("; ", $arr);
    }


    private function appendMissingESubfields(\File_MARC_Data_Field &$field)
    {
        $types = [
            'aut' => 'Verfasser',
            'hnr' => 'Gefeierter',
            'prf' => 'Ausf&uuml;hrender'
        ];

        /** @var \File_MARC_Subfield $_4 */
        foreach ($field->getSubfields('4') as $_4) {
            if (in_array($_4->getData(), array_keys($types))) {
                $field->appendSubfield(new \File_MARC_Subfield("e", $types[$_4->getData()]));
            }
        }
    }

    protected function addLink($record, $personalName)
    {
        $url = $this->getView()->record($record)->getLink('author', $personalName);
        return '<a title="' . $personalName . '" href="' . $url . '">' . $personalName . '</a>';
    }

    /**
     * @var \File_MARC_Data_Field $field
     * @return string
     */
    public function getGnd($field)
    {
        if (!empty($field)) {
            $subfields = $field->getSubfields(0);


            $gndArray = array_filter($subfields, function ($field) {
                /** @var \File_MARC_Subfield $field */
                return strpos($field->getData(), "(DE-588)") !== false;
            });

            if (!empty($gndArray)) {
                $gnds = [];
                foreach ($gndArray as $gndId) {
                    $gnds[] = str_replace("(DE-588)", "", $gndId->getData());
                }
                return $gnds[0];
            }
        }
        return "";
    }
}
