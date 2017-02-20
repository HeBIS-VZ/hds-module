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

namespace Hebis\View\Helper\Record\BibTip;


use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\OtherEdition\OtherEditionTitleStatement;
use Hebis\View\Helper\Record\ResultList\ResultListTitleStatement;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordTitleStatement;

/**
 * Class BibTipTitleStatement
 * @package Hebis\View\Helper\Record\BibTip
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class BibTipTitleStatement extends OtherEditionTitleStatement
{

    public function __invoke(SolrMarc $record)
    {
        /* WENN $n mit [...] besetzt, DANN $p anzeigen, SONST nur $n anzeigen
           Wiederholung von $n bzw. $p: "_;_" (Blank Semikolon Blank)
           Enthält ein Subfeld in 245 eines der folgenden Sonderzeichen, dann vor diesem die Anzeige beenden: " / "  " = "  " : "
           WENN 245 $9 = patchF, DANN:
           490 $a_;_$v
           SONST:
           245 $a_;_$n_;_$p */

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $_245 */
        $_245 = $marcRecord->getField(245);
        /** @var \File_MARC_Subfield $sf */
        if ($sf = $_245->getSubfields(9)) {
            if (strpos($sf->getData(), "patchF") !== false) {
                return $this->extract490av($marcRecord);
            }
        }

        /* 245 $a_;_$n_;_$p */
        $_arr = [];
        $a = $this->flatten($_245, 'a');
        $n = $this->flatten($_245, 'n');
        $p = $this->flatten($_245, 'p');
        empty($a) ?: $_arr[] = $a;
        empty($n) ?: $_arr[] = $n;
        empty($p) ?: $_arr[] = $p;

        return implode(" ; ", $_arr);
    }

    private function flatten(\File_MARC_Data_Field $field, $subfieldCode)
    {
        $i = 0;
        $a = "";
        $subfields = $field->getSubfields($subfieldCode);
        /** @var \File_MARC_Subfield $_a */
        foreach ($subfields as $subfield) {
            $a .= $this->trimTitle($subfield);
            if ($i < count($subfields) - 1) {
                $a .= " ; ";
            }
        }
        return $a;
    }

}