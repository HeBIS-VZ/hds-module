<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 09.02.17
 * Time: 11:07
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

        array_filter($authorities, function($field){
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