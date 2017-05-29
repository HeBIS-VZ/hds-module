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

use Hebis\View\Helper\Record\MarcSubfieldManipulationTrait;
use Hebis\View\Helper\Record\ResultList\ResultListPublication;
use Hebis\RecordDriver\SolrMarc;

/**
 * Class SingleRecordPublicationDistribution
 * @package Hebis\View\Helper\Record
 *
 * @author Claudia Grote <grote@hebis.uni-frankfurt.de>
 */
class SingleRecordOWPublication extends ResultListPublication
{
    use MarcSubfieldManipulationTrait;

    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record, $asArray = false)
    {
        $ret = "";
        $id = $record->getUniqueID();

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $arr = [];

        $_880__ = $marcRecord->getFields('880');

        foreach ($_880__ as $_880) {
            $_880_6 = empty($_880) ? "" : $this->getSubFieldDataOfGivenField($_880, '6');
            if (strncmp("264", $_880_6, 3) == 0) {
                $_880c = empty($_880) ? "" : $this->getSubFieldDataOfGivenField($_880, 'c');
                $r = implode(" : ", $this->getSubFieldsStringArrayOfGivenField($_880, ['a', 'b']));
                $r .= empty($_880c) ? "" : (!empty($r) ? ", $_880c" : $_880c);
                $arr[] = $r;
            }
            if ($asArray) {
                return $arr;
            }

            return implode("<br />", $arr);
        }
    }
}
