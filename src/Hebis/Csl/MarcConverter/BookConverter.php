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

use Seboettg\CiteData\Csl\Record as Book;

class BookConverter
{
    use SubfieldsTrait;

    public static function convert(\File_MARC_Record $record)
    {
        $book = new Book();

        $book->setAuthor(Name::getAuthor($record));
        $book->setAuthority(Name::getAuthority($record));
        $book->setCollectionNumber(Record::getCollectionNumber($record));
        $book->setCollectionTitle(Record::getCollectionTitle($record));
        $book->setContainerTitle(self::getContainerTitle($record));
        $book->setEditor(Name::getEditor($record));
        $book->setTranslator(Name::getTranslator($record));
        $book->setIllustrator(Name::getIllustrator($record));
        $book->setISBN(Record::getISBN($record));
        $book->setISSN(Record::getISSN($record));
        $book->setTitle(Record::getTitle($record));
        $book->setPublisher(Record::getPublisher($record));
        $book->setPublisherPlace(Record::getPublisherPlace($record));
        $book->setIssued(Date::getIssued($record));
        $book->setNumberOfPages(Record::getNumberOfPages($record));
        $book->setDOI(Record::getDOI($record));
        $urn = Record::getURN($record);
        if (!empty($urn)) {
            $book->setURL("https://nbn-resolving.org/" . $urn);
        }
        $book->setType("book");

        return json_encode($book);
    }


    public static function getContainerTitle(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "490", "a", function ($field) {
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(1) == "0";
        });
    }
}
