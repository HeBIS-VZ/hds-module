<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.17
 * Time: 15:10
 */

namespace Hebis\Csl\MarcConverter;


class Record
{

    use SubfieldsTrait;

    /**
     * @param \File_MARC_Record $record
     * @return string|null
     */
    public static function getContainerTitle(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "490", "a");
    }


    public static function getDimensions(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "300", "c");
    }

    public static function getDOI(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "024", "a", function($field) {
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(1) === "7";
        });
    }

    public static function getEdition($record)
    {
        return self::getSubfield($record, "250", "a");
    }

    public static function getISSN($record)
    {
        return self::getSubfield($record, "490", "x");
    }


    public static function getPage($record)
    {
        $page = self::getSubfield($record, "773", "g");
        if (!empty($page) && strpos($page, ",") !== false) {
            $pos = strrpos($page, ",");
            return trim(substr($page, $pos));
        }
        return null;
    }

    public static function getPublisher($record)
    {
        return self::getSubfield($record, "264", "b");
    }

    public static function getPublisherPlace($record)
    {
        return self::getSubfield($record, "264", "a");
    }

    public static function getTitle($record)
    {
        $a = self::getSubfield($record, "245", "a");
        $b = self::getSubfield($record, "245", "b");

        return !empty($b) ? "$a : $b" : $a;
    }

    public static function getURL($record)
    {
        return self::getSubfield($record, "850", "u", function($field) {
            /** @var \File_MARC_Data_Field $field */
            return ($field->getIndicator(1) === "4" && $field->getIndicator(2) === "0")
                || ($field->getIndicator(1) === " " && $field->getIndicator(2) === " ");
        });
    }


}