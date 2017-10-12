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
            case 'image':
            case 'tableChart':
                $graphic = static::convertGraphic($record);
                return json_encode($graphic);
            case 'videoRecording':
                $video = static::converMotionPicture($record);
                return json_encode($video);
            case 'score':
                return json_encode(static::convertMusicalScore($record));
            case 'map':
                return json_encode(static::convertMap($record));
            case 'conference':
                return json_encode(static::convertConference($record));

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
        if (!empty($collection = self::getFromItems($record->getFields()["Items"], "SeriesInfo"))) {
            $book->setCollectionTitle($collection);
        }
        if (!empty($container = self::getFromItems($record->getFields()["Items"], "TitleSource"))) {
            $book->setContainerTitle($collection);
        }
        if (!empty($doi = $record->getCleanDOI())) {
            $book->setDOI($doi);
        }
        if (!empty($isbn = self::getFromItems($record->getFields()["Items"], "ISBN"))) {
            $book->setISBN($isbn);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $book->setIssued($issued);
        }
        if (!empty($pageCount = $record->getContainerPageCount())) {
            $book->setNumberOfPages($pageCount);
        }
        if (!empty($language = $record->getLanguages())) {
            $book->setLanguage($language);
        }
        if (!empty($publisher = self::getFromItems($record->getFields()["Items"], "Publisher")) ||
            !empty($publisher = self::getFromItems($record->getFields()["Items"], "PubInfo"))) {
            $book->setPublisher($publisher);
        }
        if (!empty($pubPlace = self::getFromItems($record->getFields()["Items"], "PlacePub"))) {
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

    /**
     * @param $record
     * @return Record
     */
    private static function convertGraphic($record)
    {
        $graphic = new Record();
        $graphic->setType("graphic");
        if (!empty($bibRelationships = $record->getFields()['RecordInfo']['BibRecord']['BibRelationships'])) {
            //$graphic->setAuthor(self::convertPersonEntities($bibRelationships['HasContributorRelationships']));
            $graphic->setIllustrator($graphic->getAuthor());
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships'])) {
            $graphic->setAuthority(self::convertOrganizationEntity($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships']['HasPubAgent']));
        }
        if (!empty($collection = self::getFromItems($record->getFields()["Items"], "SeriesInfo"))) {
            $graphic->setCollectionTitle($collection);
        }
        if (!empty($container = self::getFromItems($record->getFields()["Items"], "TitleSource"))) {
            $graphic->setContainerTitle($collection);
        }
        if (!empty($doi = $record->getCleanDOI())) {
            $graphic->setDOI($doi);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $graphic->setIssued($issued);
        }
        if (!empty($medium = self::getUrlFormItems($record->getFields()["Items"], "PhysDesc"))) {
            $graphic->setURL($medium);
        }
        if (!empty($publisher = self::getFromItems($record->getFields()["Items"], "Publisher")) ||
            !empty($publisher = self::getFromItems($record->getFields()["Items"], "PubInfo"))) {
            $graphic->setPublisher($publisher);
        }
        if (!empty($pubPlace = self::getFromItems($record->getFields()["Items"], "PlacePub"))) {
            $graphic->setPublisherPlace($pubPlace);
        }
        if (!empty($title = $record->getTitle())) {
            $graphic->setTitle($title);
        }
        if (!empty($url = self::getUrlFormItems($record->getFields()["Items"], "URL"))) {
            $graphic->setURL($url);
        }
        return $graphic;
    }

    /**
     * @param EDS $record
     * @return Record
     */
    private static function convertMap($record)
    {
        $map = new Record();
        $map->setType("map");
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships'])) {
            $map->setAuthor(self::convertPersonEntities($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']['HasContributorRelationships']));
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships'])) {
            $map->setAuthority(self::convertOrganizationEntity($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships']['HasPubAgent']));
        }
        if (!empty($collection = self::getFromItems($record->getFields()["Items"], "SeriesInfo"))) {
            $map->setCollectionTitle($collection);
        }
        if (!empty($container = self::getFromItems($record->getFields()["Items"], "TitleSource"))) {
            $map->setContainerTitle($collection);
        }
        if (!empty($dimension = self::getFromItems($record->getFields()["Items"], "Format"))) {
            $map->setDimensions($dimension);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $map->setIssued($issued);
        }
        if (!empty($language = $record->getLanguages())) {
            $map->setLanguage($language);
        }
        if (!empty($publisher = self::getFromItems($record->getFields()["Items"], "Publisher")) ||
            !empty($publisher = self::getFromItems($record->getFields()["Items"], "PubInfo"))) {
            $map->setPublisher($publisher);
        }
        if (!empty($pubPlace = self::getFromItems($record->getFields()["Items"], "PlacePub"))) {
            $map->setPublisherPlace($pubPlace);
        }
        if (!empty($title = $record->getTitle())) {
            $map->setTitle($title);
        }
        if (!empty($url = self::getUrlFormItems($record->getFields()["Items"], "URL"))) {
            $map->setURL($url);
        }
        return $map;
    }

    /**
     * @param EDS $record
     * @return Record
     */
    private static function converMotionPicture($record)
    {
        $video = new Record();
        $video->setType("motion_picture");
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships'])) {
            $video->setAuthor(self::convertPersonEntities($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']['HasContributorRelationships']));
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships'])) {
            $video->setAuthority(self::convertOrganizationEntity($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships']['HasPubAgent']));
        }
        if (!empty($collection = self::getFromItems($record->getFields()["Items"], "SeriesInfo"))) {
            $video->setCollectionTitle($collection);
        }
        if (!empty($container = self::getFromItems($record->getFields()["Items"], "TitleSource"))) {
            $video->setContainerTitle($collection);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $video->setIssued($issued);
        }
        if (!empty($language = $record->getLanguages())) {
            $video->setLanguage($language);
        }
        if (!empty($medium = self::getFromItems($record->getFields()["Items"], "PhysDesc"))) {
            $video->setMedium($medium);
        }
        if (!empty($publisher = self::getFromItems($record->getFields()["Items"], "Publisher")) ||
            !empty($publisher = self::getFromItems($record->getFields()["Items"], "PubInfo"))) {
            $video->setPublisher($publisher);
        }
        if (!empty($pubPlace = self::getFromItems($record->getFields()["Items"], "PlacePub"))) {
            $video->setPublisherPlace($pubPlace);
        }
        if (!empty($title = $record->getTitle())) {
            $video->setTitle($title);
        }
        if (!empty($url = self::getUrlFormItems($record->getFields()["Items"], "URL"))) {
            $video->setURL($url);
        }
        return $video;
    }

    /**
     * @param EDS $record
     * @return Record
     */
    private static function convertMusicalScore($record)
    {
        $score = new Record();
        $score->setType("musical_score");
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships'])) {
            $score->setComposer(self::convertPersonEntities($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']['HasContributorRelationships']));
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships'])) {
            $score->setAuthority(self::convertOrganizationEntity($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships']['HasPubAgent']));
        }
        if (!empty($collection = self::getFromItems($record->getFields()["Items"], "SeriesInfo"))) {
            $score->setCollectionTitle($collection);
        }
        if (!empty($container = self::getFromItems($record->getFields()["Items"], "TitleSource"))) {
            $score->setContainerTitle($collection);
        }
        if (!empty($doi = $record->getCleanDOI())) {
            $score->setDOI($doi);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $score->setIssued($issued);
        }
        if (!empty($language = $record->getLanguages())) {
            $score->setLanguage($language);
        }
        if (!empty($medium = self::getFromItems($record->getFields()["Items"], "PhysDesc"))) {
            $score->setMedium($medium);
        }
        if (!empty($publisher = self::getFromItems($record->getFields()["Items"], "Publisher")) ||
            !empty($publisher = self::getFromItems($record->getFields()["Items"], "PubInfo"))) {
            $score->setPublisher($publisher);
        }
        if (!empty($pubPlace = self::getFromItems($record->getFields()["Items"], "PlacePub"))) {
            $score->setPublisherPlace($pubPlace);
        }
        if (!empty($title = $record->getTitle())) {
            $score->setTitle($title);
        }
        if (!empty($url = self::getUrlFormItems($record->getFields()["Items"], "URL"))) {
            $score->setURL($url);
        }
        return $score;
    }

    /**
     * @param EDS $record
     * @return Record
     */
    private static function convertConference($record)
    {
        $conference = new Record();
        $conference->setType("paper-conference");
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['BibRelationships'])) {
            $conference->setAuthor(self::convertPersonEntities($record->getFields()['RecordInfo']['BibRecord']['BibRelationships']['HasContributorRelationships']));
        }
        if (!empty($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships'])) {
            $conference->setAuthority(self::convertOrganizationEntity($record->getFields()['RecordInfo']['BibRecord']['HasPubAgentRelationships']['HasPubAgent']));
        }
        if (!empty($collection = self::getFromItems($record->getFields()["Items"], "SeriesInfo"))) {
            $conference->setCollectionTitle($collection);
        }
        if (!empty($container = self::getFromItems($record->getFields()["Items"], "TitleSource"))) {
            $conference->setContainerTitle($collection);
        }
        if (!empty($doi = $record->getCleanDOI())) {
            $conference->setDOI($doi);
        }
        if (!empty($isbn = self::getFromItems($record->getFields()["Items"], "ISBN"))) {
            $conference->setISBN($isbn);
        }
        if (!empty($issue = $record->getContainerIssue())) {
            $conference->setIssue($issue);
        }
        if (!empty($issued = self::getDateParts($record))) {
            $conference->setIssued($issued);
        }
        if (!empty($pageCount = $record->getContainerPageCount())) {
            $conference->setNumberOfPages($pageCount);
        }
        if (!empty($language = $record->getLanguages())) {
            $conference->setLanguage($language);
        }
        if (!empty($publisher = self::getFromItems($record->getFields()["Items"], "Publisher")) ||
            !empty($publisher = self::getFromItems($record->getFields()["Items"], "PubInfo"))) {
            $conference->setPublisher($publisher);
        }
        if (!empty($pubPlace = self::getFromItems($record->getFields()["Items"], "PlacePub"))) {
            $conference->setPublisherPlace($pubPlace);
        }
        if (!empty($title = $record->getTitle())) {
            $conference->setTitle($title);
        }
        if (!empty($url = self::getUrlFormItems($record->getFields()["Items"], "URL"))) {
            $conference->setURL($url);
        }
        if (!empty($volume = $record->getContainerVolume())) {
            $conference->setVolume($volume);
        }
        return $conference;
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

    private static function getFromItems($items, $name)
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
        $link = self::getFromItems($items, $string);
        if (!empty($link)) {
            if (preg_match("/linkTerm=\"(.+)\"\slinkWindow/", html_entity_decode($link), $match)) {
                return $match[1];
            }
        }
        return null;
    }

}
