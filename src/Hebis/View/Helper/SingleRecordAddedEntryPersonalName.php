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

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

/**
 * Class SingleRecordAddedEntryPersonalName
 * @package Hebis\View\Helper
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordAddedEntryPersonalName extends AbstractRecordViewHelper
{

    public function authorSearchLink($author)
    {
        if (empty($author)) {
            return $author;
        }
        $href = sprintf(parent::URL_AUTHOR_SEARCH_PATTERN, urlencode(trim($author)));
        return parent::generateLink($href, $author, $author);
    }

    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $_700 = $marcRecord->getFields('700');
        $fields = array_filter(
            $_700,
            function(\File_MARC_Data_Field $field) {
                $subField = $field->getSubfield('4');
                return !empty($subField) && !in_array($subField->getData(), ['aut', 'hnr', 'prf']);
            }
        );

        return implode(" ; ", $this->extractContents($fields));
    }

    /**
     * @param $fields
     * @return array
     */
    protected function extractContents($fields)
    {
        $arr = [];

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {

            /** @var string $ret */
            $ret = "";

            list($a, $b) = $this->extractDataFromSubFields($field, ['a', 'b']);
            $c = $this->getSubFieldDataOfGivenField($field, 'c');
            $e = $this->getSubFieldDataOfGivenField($field, 'e');

            $ret .= $a ? $a : "";
            $ret .= $b ? " $b" : "";
            $ret .= $c ? " <$c>" : "";

            $ret .= $e ? " ($e)" : "";

            $arr[] = $this->authorSearchLink($ret);

        }

        return $arr;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @param array
     * @return array
     */
    private function extractDataFromSubFields($field, $subFieldNos)
    {
        $arr = [];

        foreach ($subFieldNos as $subFieldNo) {

            $subField = $field->getSubfield($subFieldNo);
            $arr[] = $subField ? $subField->getData() : null;
        }
        return $arr;
    }

}