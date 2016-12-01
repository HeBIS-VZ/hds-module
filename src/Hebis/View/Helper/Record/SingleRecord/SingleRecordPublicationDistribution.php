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

use Hebis\View\Helper\Record\ResultList\ResultListPublicationDistribution;
use Hebis\RecordDriver\SolrMarc;

/**
 * Class SingleRecordPublicationDistribution
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordPublicationDistribution extends ResultListPublicationDistribution
{
    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $fields = $marcRecord->getFields('260');
        $fields_ = [];

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            if ($field->getIndicator(1) != "3") {
                array_push($fields_, $field); //push at the end of the array
            } else {
                array_unshift($fields_, $field); //prepend field at the beginning of the array
            }
        }

        $arr = [];

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields_ as $field) {
            $str = "";
            /** @var \File_MARC_Subfield $subField */
            $ind1 = $field->getIndicator(1);
            $ind2 = $field->getIndicator(2);
            foreach ($field->getSubfields() as $subField) {
                $key = $subField->getCode();

                switch ($key) {
                    case 'a':
                        $str .= htmlentities($subField->getData());
                        break;
                    case 'b':
                        $str .= " : " . htmlentities($subField->getData());
                        break;
                    case 'c':
                        $str .= ", " . htmlentities($subField->getData());
                        break;
                    case 'e':
                        $str .= " - (" . htmlentities($subField->getData());
                        break;
                    case 'f':
                        $str .= htmlentities($subField->getData()) . ")";
                        break;
                }

            }
            $arr[] = trim($str);
        }

        return implode("<br />", $arr);
    }


}