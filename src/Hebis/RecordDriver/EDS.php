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

namespace Hebis\RecordDriver;

/**
 * Class EDS
 * @package Hebis\RecordDriver
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class EDS extends \VuFind\RecordDriver\EDS
{

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Return a URL to a thumbnail preview of the record, if available; false
     * otherwise.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string
     */
    public function getThumbnail($size = 'small')
    {
        if (!empty($this->fields['ImageInfo'])) {
            foreach ($this->fields['ImageInfo'] as $image) {
                if (isset($image['Size']) && $size == $image['Size']) {
                    return (isset($image['Target'])) ? $image['Target'] : '';
                }
            }
        }
        return false;
    }

    public function getCleanDOI()
    {
        if (!empty($bibEntity = $this->fields['RecordInfo']['BibRecord']['BibEntity']) &&
            array_key_exists('Identifiers', $bibEntity)) {

            foreach ($bibEntity['Identifiers'] as $identifier) {
                if (strcasecmp($identifier['Type'],"doi") === 0) {
                    return mb_convert_encoding($identifier['Value'], "UTF-8");
                }
            }
        }
        return false;
    }

    public function getContainerIssue()
    {
        return $this->getNumbering("issue");
    }

    public function getContainerVolume()
    {
        return $this->getNumbering("volume");
    }

    /**
     * @param string $type (issue|volume)
     * @return string
     */
    private function getNumbering($type)
    {
        if (!empty($bibRelationships = $this->fields['RecordInfo']['BibRecord']['BibRelationships'])) {
            if (array_key_exists('IsPartOfRelationships', $bibRelationships) &&
                !empty($bibRelationships['IsPartOfRelationships'])) {
                $bibEntity = $bibRelationships['IsPartOfRelationships'][0]['BibEntity'];
                if (array_key_exists('Numbering', $bibEntity) &&
                    !empty($bibEntity['Numbering'])) {

                    foreach ($bibEntity['Numbering'] as $numbering) {
                        if (!strcasecmp($numbering['Type'], $type)) {
                            return $numbering['Value'];
                        }
                    }
                }
            }
        }
        return false;
    }

    public function getContainerStartPage()
    {
        if (!empty($bibEntity = $this->fields['RecordInfo']['BibRecord']['BibEntity'])) {
            if (array_key_exists("PhysicalDescription", $bibEntity) && array_key_exists("Pagination", $bibEntity["PhysicalDescription"])) {
                $pagination = $bibEntity["PhysicalDescription"]["Pagination"];
                return $pagination["StartPage"];
            }
        }
        return false;
    }

    public function getContainerEndPage()
    {
        $endPage = $this->getContainerPageCount();

        if (!empty($startPage = $this->getContainerStartPage())) {
            if (!empty($endPage)) {
                return intval($startPage) + intval($endPage);
            }
            return $startPage;
        } elseif (!empty($endPage)) {
            return $endPage;
        }

        return false;
    }

    public function getContainerPageCount()
    {
        if (!empty($bibEntity = $this->fields['RecordInfo']['BibRecord']['BibEntity'])) {
            if (array_key_exists("PhysicalDescription", $bibEntity) && array_key_exists("Pagination", $bibEntity["PhysicalDescription"])) {
                $pagination = $bibEntity["PhysicalDescription"]["Pagination"];
                return mb_convert_encoding($pagination["PageCount"], "UTF-8");
            }
        }
        return false;
    }

    public function getLanguages()
    {
        if (!empty($bibEntity = $this->fields['RecordInfo']['BibRecord']['BibEntity'])) {
            if (array_key_exists("Languages", $bibEntity)) {
                $languages = $bibEntity["Languages"];
                return mb_convert_encoding($languages[0]["Text"], "UTF-8");
            }
        }
        return false;
    }

    /**
     * Obtain the title of the record from the record info section
     *
     * @return string
     */
    public function getTitle()
    {
        if (isset($this->fields['RecordInfo']['BibRecord']['BibEntity']['Titles'])) {
            foreach ($this->fields['RecordInfo']['BibRecord']['BibEntity']['Titles']
                     as $titleRecord
            ) {
                if (isset($titleRecord['Type']) && 'main' == $titleRecord['Type']) {
                    return mb_convert_encoding($titleRecord['TitleFull'], "UTF-8");
                }
            }
        }
        foreach ($this->fields["Items"] as $item) {
            if ($item["Name"] === "Title") {
                return mb_convert_encoding($item["Data"], "UTF-8");
            }
        }
        return '';
    }
}
