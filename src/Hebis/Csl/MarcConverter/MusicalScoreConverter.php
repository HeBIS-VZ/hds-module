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

use Hebis\Csl\Model\Record as MusicalScore;

class MusicalScoreConverter
{

    public static function convert(\File_MARC_Record $record)
    {
        $musicalScore = new MusicalScore();
        $musicalScore->setType("musical_score");
        $musicalScore->setAuthor(Name::getAuthor($record));
        $musicalScore->setAuthority(self::getAuthority($record));
        $musicalScore->setCollectionNumber(Record::getCollectionNumber($record));
        $musicalScore->setCollectionTitle(Record::getCollectionTitle($record));
        $musicalScore->setComposer(Name::getComposer($record));
        $musicalScore->setContainerTitle(Record::getContainerTitle($record));
        $musicalScore->setDOI(Record::getDOI($record));
        $musicalScore->setEdition(Record::getEdition($record));
        $musicalScore->setEditor(Name::getEditor($record));
        $musicalScore->setIllustrator(Name::getIllustrator($record));
        $musicalScore->setISBN(Record::getISBN($record));
        $musicalScore->setISSN(Record::getISSN($record));
        $musicalScore->setIssued(Date::getIssued($record));
        $musicalScore->setNumberOfPages(Record::getNumberOfPages($record));
        $musicalScore->setPublisher(Record::getPublisher($record));
        $musicalScore->setPublisherPlace(Record::getPublisherPlace($record));
        $musicalScore->setTitle(Record::getTitle($record));
        $musicalScore->setURL(Record::getURL($record));
        $musicalScore->setVolume(Record::getVolume($record));

        return json_encode($musicalScore);
    }

    private static function getAuthority(\File_MARC_Record $record)
    {
        $authorities = $record->getFields("710");

        array_filter($authorities, function ($field) {
            /** @var \File_MARC_Data_Field $field */
            $_4 = $field->getSubfield('4');
            return $field->getIndicator(2) === " " &&
                ($_4->getData() === "aut" || $_4->getData() === "cmp");
        });

        $names = [];
        foreach ($authorities as $authority) {
            $names[] = Name::extractName($authority);
        }
        return $names;
    }
}