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
use Hebis\View\Helper\Record\CorporateHelperTrait;
use Hebis\View\Helper\Record\CorporateNameTrait;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordCorporateName;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordPersonalName;

/**
 * Class BibTipPersonalName
 * @package Hebis\View\Helper\Record\BibTip
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class BibTipPersonalName extends SingleRecordPersonalName
{

    use CorporateNameTrait;
    use CorporateHelperTrait;

    public function __invoke(SolrMarc $record, $test = true)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $field100 = $marcRecord->getField('100');

        $aut = $this->getFieldContents($field100, false);

        if (!empty($aut)) {
            return $aut;
        }

        $aut = "";

        $f700_ = $marcRecord->getFields(700);

        if (!empty($f700_)) {
            $filteredFields = $this->filterByIndicator($f700_, 2, " ");
            $aut = $this->getFieldContents($filteredFields[0], false);
        }

        if (!empty($aut)) {
            return $aut;
        }

        $arr[] = $this->getCorporateName($record);

        if (is_array($arr[0])) {
            if (!empty($arr[0]) && isset($arr[0][0])) {
                return $arr[0][0];
            }
            return "";
        }
        return $arr[0];
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
