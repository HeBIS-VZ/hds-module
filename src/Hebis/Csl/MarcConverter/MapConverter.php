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

namespace Hebis\Csl\MarcConverter;

use Seboettg\CiteData\Csl\Record as Map;

class MapConverter
{
    use SubfieldsTrait;

    public static function convert(\File_MARC_Record $record)
    {
        $map = new Map();
        $map->setType("map");
        $map->setAuthor(self::getAuthor($record));
        $map->setCollectionNumber(Record::getCollectionNumber($record));
        $map->setCollectionTitle(Record::getCollectionTitle($record));
        $map->setContainerTitle(Record::getContainerTitle($record));
        $map->setDimensions(Record::getDimensions($record));
        $map->setEdition(Record::getEdition($record));
        $map->setEditor(Name::getEditor($record));
        $map->setIllustrator(Name::getIllustrator($record));
        $map->setISBN(self::getISBN($record));
        $map->setISSN(Record::getISSN($record));
        $map->setIssued(Date::getIssued($record));
        $map->setPublisher(Record::getPublisher($record));
        $map->setPublisherPlace(Record::getPublisherPlace($record));
        $map->setScale(self::getScale($record));
        $map->setTitle(Record::getTitle($record));
        $map->setVolume(Record::getVolume($record));
        return json_encode($map);
    }

    private static function getAuthor(\File_MARC_Record $marcRecord)
    {
        $authors = [];
        $marc100 = $marcRecord->getFields('100');

        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $authors[] = Name::extractName($field);
            }
        }
        $marc700 = $marcRecord->getFields('700');

        array_filter($marc700, function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $ind2 == " " &&
                    $_4->getData() === "aut" || $_4->getData() === "ctg";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $authors[] = Name::extractName($field);
            }
        }
        return $authors;
    }

    private static function getScale(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "255", "a");

    }

    private static function getISBN($record)
    {
        $_9 = self::getSubfield($record, "020", "9");
        return empty($_9) ? self::getSubfield($record, "020", "a") : $_9;
    }
}
