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


trait FieldArray
{

    protected function getFieldArray(\File_MARC_Record $record, $field, $subFields = null, $concat = true)
    {
        // Default to subfield a if nothing is specified.
        if (!is_array($subFields)) {
            $subFields = ['a'];
        }

        // Initialize return array
        $matches = [];

        // Try to look up the specified field, return empty array if it doesn't
        // exist.
        $fields = $record->getFields($field);
        if (!is_array($fields)) {
            return $matches;
        }

        // Extract all the requested subfields, if applicable.
        foreach ($fields as $currentField) {
            $next = $this->getSubfieldArray($currentField, $subFields, $concat);
            $matches = array_merge($matches, $next);
        }

        return $matches;
    }

    /**
     * Return an array of non-empty subfield values found in the provided MARC
     * field.  If $concat is true, the array will contain either zero or one
     * entries (empty array if no subfields found, subfield values concatenated
     * together in specified order if found).  If concat is false, the array
     * will contain a separate entry for each subfield value found.
     *
     * @param object $currentField Result from File_MARC::getFields.
     * @param array $subFields The MARC subField codes to read
     * @param bool $concat Should we concatenate subFields?
     *
     * @return array
     */
    protected function getSubFieldArray($currentField, $subFields, $concat = true)
    {
        // Start building a line of text for the current field
        $matches = [];
        $currentLine = '';

        // Loop through all subfields, collecting results that match the whitelist;
        // note that it is important to retain the original MARC order here!
        $allSubFields = $currentField->getSubfields();
        if (count($allSubFields) > 0) {
            foreach ($allSubFields as $currentSubField) {
                if (in_array($currentSubField->getCode(), $subFields)) {
                    // Grab the current subField value and act on it if it is
                    // non-empty:
                    $data = trim($currentSubField->getData());
                    if (!empty($data)) {
                        // Are we concatenating fields or storing them separately?
                        if ($concat) {
                            $currentLine .= $data . ' ';
                        } else {
                            $matches[] = $data;
                        }
                    }
                }
            }
        }

        // If we're in concat mode and found data, it will be in $currentLine and
        // must be moved into the matches array.  If we're not in concat mode,
        // $currentLine will always be empty and this code will be ignored.
        if (!empty($currentLine)) {
            $matches[] = trim($currentLine);
        }

        // Send back our result array:
        return $matches;
    }

    /**
     * @param array $fields
     * @param array $subFields
     * @return array
     */
    protected function getFieldSubFieldArray($fields, $subFields)
    {
        // Default to subfield a if nothing is specified.
        if (!is_array($subFields)) {
            return array();
        }

        // Initialize return array
        $matches = array();

        // Try to look up the specified field, return empty array if it doesn't
        // exist.


        // Extract all the requested subfields, if applicable.
        $tmp = array();

        /** @var \File_MARC_Data_Field $currentField */
        foreach ($fields as $currentField) {
            $fsubfields = $currentField->getSubfields();
            foreach ($fsubfields as $fsubfield) {
                $code = trim($fsubfield->getCode());
                $data = trim($fsubfield->getData());
                if (in_array($code, $subFields)) {
                    $tmp[] = array($code, $data);
                }
            }
            if (count($tmp) > 0) {
                $matches[] = $tmp;
                $tmp = array();
            }
        }
        return $matches;
    }
}