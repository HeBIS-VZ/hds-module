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
use Hebis\View\Helper\Record\ResultList\ResultListCorporateName;


/**
 * Class SingleRecordCorporateName
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordCorporateName extends ResultListCorporateName
{

    public function __invoke(SolrMarc $record, $asArray = false)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $arr = [];
        $_110 = $marcRecord->getField(110);
        if (!empty($_110)) {
            $subFields = $this->getSubfieldsAsArray($_110);
            $str = $this->getAbgn($subFields);
            $e = $this->expandSubfield($_110->getSubfields('e'));
            $str .= !empty($e) ? " ($e)" : "";
            $arr[] = $str;
        }

        $_111 = $marcRecord->getField(111);

        if (!empty($_111)) {
            $subFields = $this->getSubfieldsAsArray($_111);
            $str = $this->getAeg($subFields);

            $ndc = $this->getNdc($subFields);

            $str .= " (" . implode(" : ", $ndc) . ")";
            $j = $this->expandSubfield($_111->getSubfields('j'));
            $str .= !empty($j) ? " ($j)" : "";
            $arr[] = $str;
        }

        /* 710 und 711 nur auswerten wenn Indikator 2 = # */

        $_710_ = $marcRecord->getFields(710); //710 wiederholbar
        /** @var \File_MARC_Data_Field $_710 */
        foreach ($_710_ as $_710) {
            if (!empty($_710) && ord($_710->getIndicator(2)) == 32) {
                $subFields = $this->getSubfieldsAsArray($_710);
                $str = $this->getAbgn($subFields);
                $e = $this->expandSubfield($_710->getSubfields('e'));
                $str .= !empty($e) ? " ($e)" : "";
                $arr[] = $str;
            }
        }

        $_711_ = $marcRecord->getFields(711); //711 wiederholbar
        foreach ($_711_ as $_711) {
            if (!empty($_711) && ord($_711->getIndicator(2)) == 32) {
                $subFields = $this->getSubfieldsAsArray($_711);

                $str = $this->getAeg($subFields);
                $ndc = $this->getNdc($subFields);
                $str .= " (" . implode(" : ", $ndc) . ")";

                $j = $this->expandSubfield($_711->getSubfields('j'));
                $str .= !empty($j) ? " ($j)" : "";

                $arr[] = $str;
            }
        }
        if ($asArray === true) {
            return $arr;
        }

        return implode("<br />", $arr);
    }

    public function toStringArray($subFields)
    {
        $arr = [];
        /** @var \File_MARC_Subfield $subfield */
        foreach ($subFields as $subfield) {
            $arr[] = htmlentities($subfield->getData());
        }
        return $arr;
    }
}