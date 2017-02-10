<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 09.02.17
 * Time: 14:04
 */

namespace Hebis\Csl\MarcConverter;
use Hebis\Csl\Model\Record as ConferencePaper;

class PaperConferenceConverter
{

    public static function convert(\File_MARC_Record $record)
    {
        $confPaper = new ConferencePaper();
        $confPaper->setAuthority(self::getAuthority($record));
        $confPaper->setCollectionNumber(Record::getCollectionNumber($record));
        $confPaper->setCollectionTitle(Record::getCollectionTitle($record));
        $confPaper->setContainerTitle(Record::getContainerTitle($record));
        $confPaper->setDOI(Record::getDOI($record));
        $confPaper->setEdition(Record::getEdition($record));
        $confPaper->setEditor(Name::getEditor($record));
        $confPaper->setEvent(self::getEvent($record));
    }

    private static function getAuthority(\File_MARC_Record $marcRecord)
    {
        $authorities = [];
        $marc100 = $marcRecord->getFields('111');
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $authorities[] = Name::extractName($field);
            }
        }
        $marc700 = $marcRecord->getFields('711');

        array_filter($marc700, function($field){
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            if (!empty($_4)) {
                return $ind2 == " ";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $authorities[] = Name::extractName($field);
            }
        }
        return $authorities;
    }

    private static function getEvent($record)
    {

    }
}