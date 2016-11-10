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
 * Class ResultListPublicationDistribution
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class ResultListPublicationDistribution extends SingleRecordPublicationDistribution
{

    /**
     *
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /* wenn 264 Indikator 2 = 1, dann anzeigen wie folgt:
        264 $a_:_$b,_$c
        Wenn 533 $d vorhanden, dann wie folgt anzeigen:
        264 $a_:_$b,_533 $d

        Bei mehr als eine 264 mit Indikator 2 = 1 nur eine anzeigen; Priorisierung wie folgt:

        264 Indikator 1 = 3 und Indikator 2 = 1
        264 Indikator 1 = # und Indikator 2 = 1

        Kommen $a und/oder $b mehrfach vor, dann Trennzeichen: ";_" (in Worten: Semikolon Blank)
        */
        $ret = "";

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $_264 */
        $_264 = $marcRecord->getFields('264');

        $_264 = $this->filterByIndicator($_264, 2, "1");

        $_533d = $this->getSubFieldDataOfField($record, '533', 'd');

        if (count($_264) == 1) {
            $ret .= $this->concatSubfields(current($_264), $_533d);
        } else {
            $_264_ = $this->filterByIndicator($_264, 1, "3");
            $_264_ = current($_264_);
            $ret .= $this->concatSubfields($_264_, $_533d);

            $_264_ = $this->filterByIndicator($_264, 1, "");
            $_264_ = current($_264_);
            $ret .= $this->concatSubfields($_264_, $_533d);
        }

        return $ret;

    }
}