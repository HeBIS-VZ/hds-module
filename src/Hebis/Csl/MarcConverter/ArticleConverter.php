<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.17
 * Time: 14:13
 */

namespace Hebis\Csl\MarcConverter;


use Hebis\Csl\MarcConverter\Name;
use Hebis\Csl\MarcConverter\Record;
use Hebis\Csl\Model\Record as Article;

class ArticleConverter
{
    public static function convert(\File_MARC_Record $record)
    {
        $article = new Article();

        $article->setAuthor(Name::getAuthor($record));
        //$article->setAuthority(Name::getAuthority($record));
        $article->setContainerTitle(Record::getContainerTitle($record));
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
        $article->setType("article");
        return json_encode($article);
    }
}
