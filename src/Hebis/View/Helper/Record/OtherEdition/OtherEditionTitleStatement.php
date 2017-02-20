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
use Hebis\View\Helper\Record\AbstractRecordViewHelper;

/**
 * Class OtherEditionTitleStatement
 * @package Hebis\View\Helper\Record\OtherEdition
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class OtherEditionTitleStatement extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /*
        WENN 245 $9 = patchF, DANN:
        490 $a_;_$v
        SONST:
        245 $a
        Enthält 245 $a eines der folgenden Sonderzeichen, dann vor diesem die Anzeige beenden: " / "  " = "  " : "
        */
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

        return $this->trimTitle($_245->getSubfield('a'));

    }

    /**
     * @param \File_MARC_Subfield $subfield
     * @return string
     */
    protected function trimTitle(\File_MARC_Subfield $subfield)
    {
        if (!empty($subfield)) {
            $_a = $subfield->getData();
            for ($j = 0; $j < strlen($_a); ++$j) {
                if (in_array($_a{$j}, ['/', '=', ':'])) {
                    $i = $j - 1;
                    $k = $j + 1;
                    if ($i >= 0 && preg_match("/\s/", $_a{$i})
                        && $k <= strlen($_a) && preg_match("/\s/", $_a{$j})
                    ) {
                        return trim(substr($_a, 0, $i));
                    }
                }
            }
            return $_a;
        }
        return "";
    }

    /**
     * @param $marcRecord
     * @return string
     */
    public function extract490av($marcRecord): string
    {
        /** @var \File_MARC_Data_Field $_490 */
        $_490 = $marcRecord->getField(490);
        $_arr = [];
        $a = $_490->getSubfield('a');
        $v = $_490->getSubfield('v');

        !empty($a) ?: $_arr[] = $a->getData();
        !empty($v) ?: $_arr[] = $v->getData();

        return implode(" ; ", $_arr);
    }
}