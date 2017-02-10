<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 09.02.17
 * Time: 16:23
 */

namespace Hebis\Csl\MarcConverter;
use Hebis\Csl\Model\Record as Map;



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

        array_filter($marc700, function($field){
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