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

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;
use Zend\Navigation;
use Zend\View\Helper\AbstractHelper;

class ResultListTitleStatement extends SingleRecordTitleStatement
{

    public function __invoke(SolrMarc $record)
    {
        $ret = "";
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $_246 */
        $_246_ = $marcRecord->getFields('246');

        if (!empty($_246_)) {
            foreach ($_246_ as $_246) {
                if ($_246->getIndicator(2) == 3) {
                    /** @var \File_MARC_Data_Field $_856 */
                    foreach($marcRecord->getFields('856') as $_856) {
                        $_3 = $_856->getSubfield('3');
                    }
                }
            }
        }
        /** @var \File_MARC_Data_Field $field */
        $field = $marcRecord->getField('245');

        $a = htmlentities(trim($this->getSubFieldDataOfGivenField($field, 'a')));
        $a = $this->removeSpecialChars($a);
        $b = htmlentities(trim($this->getSubFieldDataOfGivenField($field, 'b')));
        $h = htmlentities(trim($this->getSubFieldDataOfGivenField($field, 'h')));
        $n = htmlentities(trim($this->getSubFieldDataOfGivenField($field, 'n')));
        $n = $this->removeControlSigns($n);
        $p = htmlentities(trim($this->getSubFieldDataOfGivenField($field, 'p')));
        $p = $this->removeControlSigns($p);
        $ret .= !empty($a) ? $a : "";
        $ret .= !empty($h) ? " $h" : "";
        $ret .= !empty($b) ? " : $b" : "";

        if (!empty($n) && strpos($n, "...") === false) {
            $ret .= "<br />$n";
        }

        if (!empty($p)) {
            $ret .= ". " . $p;
        }
        return $ret;
    }
}