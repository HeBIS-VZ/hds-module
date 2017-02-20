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

namespace Hebis\View\Helper\Record\OtherEdition;

use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordPublication;

/**
 * Class OtherEditionPublication
 * @package Hebis\View\Helper\Record\OtherEdition
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class OtherEditionPublication extends SingleRecordPublication
{

    public function __invoke(SolrMarc $record, $asArray = false)
    {
        $out = "";
        /* WENN 264 Indikator 2 = 1, DANN anzeigen wie folgt:
        264 $a_:_$b,_$c

        Bei mehr als eine 264 mit Indikator 2 = 1 nur eine anzeigen; Priorisierung wie folgt:
        264 Indikator 1 = 3 und Indikator 2 = 1
        264 Indikator 1 = # und Indikator 2 = 1

        Kommen $a und/oder $b mehrfach vor, dann Trennzeichen: ";_" (in Worten: Semikolon Blank)*/

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $_264__ = $this->filterByIndicator($this->filterByIndicator($marcRecord->getFields('264'), 1, "3"), 2, "1");

        if (empty($_264__)) {
            $_264__ = $this->filterByIndicator($this->filterByIndicator($marcRecord->getFields('264'), 1, " "), 2, "1");
        }
        if (!empty($_264__)) {
            $out = $this->generateOutput(current($_264__));
        }

        return $out;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    private function generateOutput($field)
    {
        $a_ = $this->explode($field->getSubfields('a'), "; ");
        $b_ = $this->explode($field->getSubfields('b'), "; ");
        $c = $field->getSubfield('c');

        $ret = "";

        $ret .= !empty($a_) ? $a_ : "";
        $ret .= !empty($b_) ? " : " . $b_ : "";
        $ret .= !empty($c) ? ", " . $c->getData() : "";
        return $ret;

    }

    /**
     * @param array $subfields
     * @param string $delimiter
     * @return string
     */
    private function explode($subfields, $delimiter)
    {
        $ret = "";
        $n = count($subfields);
        for ($i = 0; $i < $n; ++$i) {
            /** @var \File_MARC_Subfield $subfield */
            $subfield = $subfields[0];
            $ret .= $subfield->getData();
            if ($i < $n - 1) {
                $ret .= $delimiter;
            }
        }
        return trim($ret);
    }


}