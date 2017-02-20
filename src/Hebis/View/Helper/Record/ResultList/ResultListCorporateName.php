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

namespace Hebis\View\Helper\Record\ResultList;

use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\CorporateHelperTrait;


/**
 * Class ResultListCorporateName
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class ResultListCorporateName extends AbstractRecordViewHelper
{

    use CorporateHelperTrait;

    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $ret = "";


        /*  Nur das erste der aufgeführten Felder anzeigen, das besetzt ist
            110 $a._$b_($g)_($n)
            111 $a._$e_($g)_($n_:_$d_:_$c)
            710 $a._$b_($g)_($n)
            711 $a._$e_($g)_($n_:_$d_:_$c) */

        if (!(!empty($marcRecord->getFields(100)) || !empty($marcRecord->getFields(700)))) {
            $_110 = $marcRecord->getField(110);
            if (!empty($_110)) {
                $subFields = $this->getSubfieldsAsArray($_110);
                $ret .= $this->getAbgn($subFields);
                return $ret;
            }

            $_111 = $marcRecord->getField(111);

            if (!empty($_111)) {
                $subFields = $this->getSubfieldsAsArray($_111);
                $ret = $this->getAeg($subFields);

                $ndc = $this->getNdc($subFields);

                $ret .= " (" . implode(" : ", $ndc) . ")";
                return $ret;
            }

            /* 710 und 711 nur auswerten wenn Indikator 2 = # */

            $_710_ = $marcRecord->getFields(710); //710 wiederholbar
            /** @var \File_MARC_Data_Field $_710 */
            foreach ($_710_ as $_710) {
                if (!empty($_710) && ord($_710->getIndicator(2)) == 32) {
                    $subFields = $this->getSubfieldsAsArray($_710);
                    $ret .= $this->getAbgn($subFields);
                    return $ret;
                }
            }

            $_711_ = $marcRecord->getFields(711); //711 wiederholbar
            foreach ($_711_ as $_711) {
                if (!empty($_711) && ord($_711->getIndicator(2)) == 32) {
                    $subFields = $this->getSubfieldsAsArray($_711);
                    $ret = $this->getAeg($subFields);

                    $ndc = $this->getNdc($subFields);

                    $ret .= " (" . implode(" : ", $ndc) . ")";
                    return $ret;
                }
            }
        }

        return "";
    }
}
