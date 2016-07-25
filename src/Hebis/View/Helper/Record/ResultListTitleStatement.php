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
use Zend\Navigation;

/**
 * Class ResultListTitleStatement
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class ResultListTitleStatement extends SingleRecordTitleStatement
{

    public function __invoke(SolrMarc $record)
    {
        $ret = "";
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $_856 = $marcRecord->getField('856');
        if (!empty($_856) && !empty($this->getSubFieldsDataOfField($_856, ['3']))) {
            $tmp = $this->getSubFieldsDataOfField($_856, ['3']);
            $tmp = array_key_exists(3, $tmp) && count($tmp[3]) > 0 ? $tmp[3][0] : "";
            if (!empty($tmp) && strpos($tmp, "Katalogkarte") !== false) {
                /** @var \File_MARC_Data_Field $_246 */
                $_246_ = $marcRecord->getFields('246');
                if (!empty($_246_)) {
                    foreach ($_246_ as $_246) {
                        //if ($_246->getIndicator(2) == 3) {
                            foreach ($this->getSubFieldDataArrayOfGivenField($_246, 'a') as $a) {
                                $ret .= "[$a]<br />";
                            }

                        //}
                    }
                }
            }
        }



        /** @var \File_MARC_Data_Field $field */
        $field = $marcRecord->getField('245');

        $a = $this->getSubFieldDataArrayOfGivenField($field, 'a')[0];
        $a = $this->removeSpecialChars($a);
        $b = $this->getSubFieldDataArrayOfGivenField($field, 'b');
        $h = $this->getSubFieldDataArrayOfGivenField($field, 'h');
        $b = array_key_exists(0, $b) ? $this->getSubFieldDataArrayOfGivenField($field, 'b')[0] : "";
        $h = array_key_exists(0, $h) ? $this->getSubFieldDataArrayOfGivenField($field, 'h')[0] : "";

        $n_s = $field->getSubfields('n');
        $p_s = $field->getSubfields('p');
        $n_p = "";
        if (!empty($n_s) || !empty($p_s)) {
            $n_p .= "<br />";
        }


        for ($i = 0; $i < count($n_s); ++$i) {
            $n = array_key_exists($i, $n_s) ? $this->removeControlSigns($n_s[$i]->getData()) : "";
            $p = array_key_exists($i, $p_s) ? $this->removeControlSigns($p_s[$i]->getData()) : "";

            if (!empty($n) && strpos($n, "...") === false) {
                $n_p .= htmlentities(trim($n));
            }

            if (!empty($p)) {
                if (!in_array(substr(trim($n_p), -1), ['.', ','])) {
                    $n_p .= ". ";
                }
                $n_p .= htmlentities(trim($p));
            }

            if ($i < count($n_s)-1) {
                if (!empty($n_s[$i]->getData()) || !empty($p_s[$i]->getData())) {
                    $n_p .= "<br />";
                }
            }
        }
        $ret .= !empty($a) ? trim($a) : "";
        $ret .= !empty($h) ? " ".trim($h) : "";
        $ret .= !empty($b) ? " : ".trim($b) : "";
        $ret .= $n_p;

        return str_replace("  ", " ", $ret);
    }
}