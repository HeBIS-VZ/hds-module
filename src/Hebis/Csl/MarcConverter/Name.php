<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.17
 * Time: 11:19
 */

namespace Hebis\Csl\MarcConverter;
use Hebis\Csl\Model;
class Name
{
    use SubfieldsTrait;
    /**
     * @param \File_MARC_Record $marcRecord
     * @return array
     */
    public static function getAuthor(\File_MARC_Record $marcRecord)
    {
        $authors = [];
        $marc100 = $marcRecord->getFields('100');
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $authors[] = self::extractName($field);
            }
        }
        $marc700 = $marcRecord->getFields('700');

        array_filter($marc700, function($field){
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "aut" && $ind2 == " ";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $authors[] = self::extractName($field);
            }
        }
        return $authors;
    }

    private static function extractName(\File_MARC_Data_Field $field)
    {
        $name = new Model\Name();
        $a = $field->getSubfield('a');
        $b = $field->getSubfield('b');
        $c = $field->getSubfield('c');

        if (!empty($a)) {
            $autstr = $a->getData();
            $a_ = explode(", ", $autstr);
            $name->setFamily($a_[0]);
            if (count($a_) > 1) {
                array_shift($a_);
                $name->setGiven(implode(" ", $a_));
            }

        }

        if (!empty($b)) {
            $name->setSuffix($b->getData());
        }

        if (!empty($c)) {
            $name->setFamily($name->getFamily() . " [".$c->getData()."]");
        }

        return $name;
    }

    public static function getEditor(\File_MARC_Record $marcRecord)
    {
        $editor = [];

        $marc700 = $marcRecord->getFields('700');

        array_filter($marc700, function($field){
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "edt" && $ind2 == " ";
            }
            return false;
        });

        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $editor[] = self::extractName($field);
            }
        }
        return $editor;
    }

    public static function getIllustrator(\File_MARC_Record $marcRecord)
    {
        $filterIll = function($field){
            /** @var $field \File_MARC_Data_Field */
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "ill";
            }
            return false;
        };

        $illustrator = [];
        $marc100 = $marcRecord->getFields('100');
        array_filter($marc100, $filterIll);
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $illustrator[] = self::extractName($field);
            }
        }

        $marc700 = $marcRecord->getFields('700');
        array_filter($marc700, function($field){
            /** @var $field \File_MARC_Data_Field */
            $ind = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "ill" && $ind == " ";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $illustrator[] = self::extractName($field);
            }
        }
        return $illustrator;
    }

    public static function getAuthority($record)
    {
        return null;
    }

    public static function getTranslator(\File_MARC_Record $record)
    {
        $translators = $record->getFields("700");

        array_filter($translators, function($field){
            /** @var \File_MARC_Data_Field $field */
            $_4 = $field->getSubfield('4');
            return $field->getIndicator(2) === " " && $_4->getData() === "trl";
        });

        $names = [];
        foreach ($translators as $translator) {
            $names[] = self::extractName($translator);
        }
        return $names;
    }
}