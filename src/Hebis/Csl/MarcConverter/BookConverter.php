<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.02.17
 * Time: 13:36
 */
namespace Hebis\Csl\MarcConverter;

use \Hebis\Csl\Model\Record as Book;

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
        $book->setIllustrator(Name::getIllustrator($record));
        $book->setISBN(Record::getISBN($record));
        $book->setISSN(Record::getISSN($record));

        return $book;
    }


    public static function getContainerTitle(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "490", "a", function($field){
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(1) == "0";
        });
    }
}