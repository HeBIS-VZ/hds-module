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

namespace Hebis\Csl\EdsConverter;

use Hebis\Csl\Model\Date;
use Hebis\Csl\Model\Name;
use Hebis\Csl\Model\Record;
use Hebis\RecordDriver\ContentType;
use Hebis\RecordDriver\EDS;
use Hebis\RecordDriver\SolrMarc;

/**
 * Class Converter
 * @package Hebis\Csl\EdsConverter
 * @author
 */
class Converter
{

    public static function convert(EDS $record)
    {

        $type = $record->getPubTypeId();

        switch ($type) {
            case 'featureArticle':
            case 'academicJournal':
            case 'serialPeriodical':
                return json_encode(static::convertArticle($record));
            case 'newspaperArticle':
            case 'transcript':
                $newspaperArticle = static::convertArticle($record);
                $newspaperArticle->setType("newspaper-article");
                return json_encode($newspaperArticle);
            case 'book':
                $book = static::convertBook($record);
                return json_encode($book);
            case 'musicalscore':
                //return MusicalScoreConverter::convert($record->getMarcRecord());
            case 'map':
                //return MapConverter::convert($record->getMarcRecord());


            default:
                /*
                if (self::isThesis($record->getMarcRecord())) {
                    return ThesisConverter::convert($record->getMarcRecord());
                }
                return BookConverter::convert($record->getMarcRecord());
                */
        }
    }


    public static function convertArticle(EDS $record)
    {
        $article = new Record();
        $article->setType("article");

        if (!empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships'])) {
            $article->setAuthor(self::convertPersonEntities($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']['HasContributorRelationships']));
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships'])) {
            $article->setAuthority(self::convertOrganizationEntity($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships']['HasPubAgent']));
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']) &&
            !empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']['IsPartOfRelationships'])) {
            $article->setContainerTitle(self::convertBibEntity($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']));
        }
        if (!empty($doi = $record->getCleanDOI())) {
            $article->setDOI($doi);
        }
        if (!empty($issue = $record->getContainerIssue())) {
            $article->setIssue($issue);
        }
        if (!empty($volume = $record->getContainerVolume())) {
            $article->setVolume($volume);
        }
        if (!empty($title = $record->getTitle())) {
            $article->setTitle($title);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $article->setIssued($issued);
        }
        $endPage = $record->getContainerEndPage();
        if (!empty($startPage = $record->getContainerStartPage()) || !empty($endPage)) {
            $article->setPage($startPage . "-" . $endPage);
        } elseif (!empty($endPage)) {
            $article->setPage($startPage);
        }


        return $article;
    }

    /**
     * @param EDS $record
     * @return Record
     */
    private static function convertBook($record)
    {
        $book = new Record();
        $book->setType("book");
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships'])) {
            $book->setAuthor(self::convertPersonEntities($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']['HasContributorRelationships']));
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships'])) {
            $book->setAuthority(self::convertOrganizationEntity($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships']['HasPubAgent']));
        }
        if (!empty($collection = self::getFormItems($record->getFields()["Items"], "SeriesInfo"))) {
            $book->setCollectionTitle($collection);
        }
        if (!empty($container = self::getFormItems($record->getFields()["Items"], "TitleSource"))) {
            $book->setContainerTitle($collection);
        }
        if (!empty($doi = $record->getCleanDOI())) {
            $book->setDOI($doi);
        }
        if (!empty($isbn = self::getFormItems($record->getFields()["Items"], "ISBN"))) {
            $book->setISBN($isbn);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $book->setIssued($issued);
        }
        if (!empty($pageCount = $record->getContainerPageCount())) {
            $book->setNumberOfPages($pageCount);
        }
        if (!empty($publisher = self::getFormItems($record->getFields()["Items"], "Publisher")) ||
            !empty($publisher = self::getFormItems($record->getFields()["Items"], "PubInfo"))) {
            $book->setPublisher($publisher);
        }
        if (!empty($pubPlace = self::getFormItems($record->getFields()["Items"], "PlacePub"))) {
            $book->setPublisherPlace($pubPlace);
        }
        if (!empty($title = $record->getTitle())) {
            $book->setTitle($title);
        }
        if (!empty($url = self::getUrlFormItems($record->getFields()["Items"], "URL"))) {
            $book->setURL($url);
        }
        return $book;

    }


    private static function convertPersonEntities($personEntities)
    {
        $authors = [];
        array_map(function(&$personEntity) use (&$authors) {
            $author = new Name();
            $name = $personEntity['PersonEntity']['Name']['NameFull'];
            $delimiterPos = strpos($name, ', ');
            $author->setFamily(substr($name, 0, $delimiterPos - 1));
            $author->setGiven(substr($name, $delimiterPos + 2));
            $authors[] = $author;
        }, $personEntities);

        return $authors;
    }


    private static function convertOrganizationEntity(array $pubAgents)
    {
        $authorities = [];
        array_map(function(&$pubAgent) use (&$authorities) {
            $authorities[] = $pubAgent['OrganizationEntity']['Name']['FullName'];
        }, $pubAgents);
        return implode($authorities, "; ");
    }

    private static function convertBibEntity($bibRelationships)
    {
        return $bibRelationships['IsPartOfRelationships'][0]['BibEntity']['Titles'][0]['TitleFull'];
    }

    /**
     * @param EDS $record
     * @return Date|bool
     */
    private static function getDateParts($record)
    {
        if (!empty($bibRelationships = $record->getFields()['RecordInfo']['BibRecord']['BibRelationships']) &&
            !empty($bibRelationships['IsPartOfRelationships'])) {
            $bibEntity = $bibRelationships['IsPartOfRelationships'][0];
            if (array_key_exists("Dates", $bibEntity['BibEntity'])) {
                $dates = $bibEntity['BibEntity']['Dates'][0];
                $issued = new Date();
                if ($dates['Type'] == "published") {
                    $dateParts = [];
                    uasort($dates, function ($a, $b) {
                        return -1 * strcmp($a, $b);
                    });
                    array_walk($dates, function ($value, $key) use (&$dateParts) {
                        if (in_array($key, ['Y', 'M', 'D'])) {
                            $dateParts[] = [$value];
                        }
                    });
                    $issued->setDateParts($dateParts);
                    return $issued;
                }
            }
        }
        return false;
    }

    private static function getFormItems($items, $name)
    {
        foreach ($items as $item) {
            if ($item["Name"] === $name) {
                return strip_tags($item["Data"]);
            }
        }
        return null;
    }

    private static function getUrlFormItems($items, $string)
    {
        $link = self::getFormItems($items, $string);
        if (!empty($link)) {
            if (preg_match("/linkTerm=\"(.+)\"\slinkWindow/", html_entity_decode($link), $match)) {
                return $match[1];
            }
        }
        return null;
    }


}
