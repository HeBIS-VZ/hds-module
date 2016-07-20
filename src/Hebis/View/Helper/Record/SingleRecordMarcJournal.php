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

class SingleRecordMarcJournal extends AbstractRecordViewHelper
{
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var string $leader */
        $leader = $marcRecord->getLeader();

        $ret = '';

        if ($leader[19] === ' ') {
            $fields = $marcRecord->getFields('773');

            /** @var \File_MARC_Data_Field $field */
            foreach ($fields as $i => $field) {
                $a = $this->getSubFieldDataOfGivenField($field, 'a');
                $t = $this->getSubFieldDataOfGivenField($field, 't');
                $g = $this->getSubFieldDataOfGivenField($field, 'g');

                $w = $this->getSubFieldDataOfGivenField($field, 'w'); // ppn with prefix (DE-603)

                if ($w) {
                    if ($a) {
                        $a = '<a href="'.$this->getView()->basePath().'/Search/Results?lookfor=HEB' .
                            $this->removePrefix($w, '(DE-603)') .
                            '&amp;type=id">'.htmlentities($a).'</a>';
                    }

                    if ($t) {
                        $t = '<a href="'.$this->getView()->basePath().'/Search/Results?lookfor=HEB' .
                            $this->removePrefix($w, '(DE-603)') .
                            '&amp;type=id">' . htmlentities($t) . '</a>';
                    }
                }

                $ret .= $a ? "$a: " : "";
                $ret .= $t ? "$t" : "";
                $ret .= $g ? ", $g" : "";
            }

            //TODO: Move in a separate ViewHelper
            /*
            $ret .= '<p>' .
                    '<a href="' .
                    $this->getView()->basePath() . '/Search/Results?lookfor=HEB' .
                    $this->removePrefix($_773_w, '(DE-603)') . '&amp;type=part_of&sort=pub_date_max+desc">' .
                    $this->getView()->transEsc('alle Artikel anzeigen') .
                    '</a></p>';
            */
            return $ret;
        }

    }
}