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

use Hebis\Marc\Helper;
use Seboettg\CiteData\Csl\Record as Article;

class ArticleConverter
{
    public static function convert(\File_MARC_Record $record)
    {
        $article = new Article();

        $article->setAuthor(Name::getAuthor($record));
        //$article->setAuthority(Name::getAuthority($record));
        $article->setContainerTitle(self::getContainerTitle($record));
        $article->setDimensions(Record::getDimensions($record));
        $article->setDOI(Record::getDOI($record));
        $article->setEdition(Record::getEdition($record));
        $article->setISSN(Record::getISSN($record));
        $article->setIssued(Date::getIssued($record));
        // $article->setIssued(Record::getIssued($record));
        //$article->setLanguage();
        $article->setPage(Record::getPage($record));
        $article->setPublisher(Record::getPublisher($record));
        $article->setPublisherPlace(Record::getPublisherPlace($record));
        $article->setTitle(Record::getTitle($record));
        $article->setURL(Record::getURL($record));

        if (!empty($issue = Record::getIssue($record))) {
            $article->setIssue($issue);
        }

        if (!empty($volume = Record::getVolume($record))) {
            $article->setVolume($volume);
        }


        $article->setType("article-journal");
        return json_encode($article);
    }

    private static function getContainerTitle($record)
    {
        return Helper::getSubFieldDataOfField($record, "773", "t");
    }
}
