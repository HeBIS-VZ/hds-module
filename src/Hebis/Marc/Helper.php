<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 06.01.17
 * Time: 16:28
 */

namespace Hebis\Marc;


use Hebis\RecordDriver\SolrMarc;

class Helper
{
    /**
     * returns the data of the subField if field and subField exists, otherwise false
     *
     * @param SolrMarc $record
     * @param $fieldCode
     * @param $subFieldCode
     * @return bool|string
     */
    public static function getSubFieldDataOfField(SolrMarc $record, $fieldCode, $subFieldCode)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $field */
        $field = $marcRecord->getField($fieldCode);

        return self::getSubFieldDataOfGivenField($field, $subFieldCode);
    }

    /**
     * if field of type \File_MARC_Data_Field and it has a subField with $subFieldCode this function returns the data
     * string of the subField, otherwise false.
     *
     * @param $field
     * @param $subFieldCode
     * @return bool|string
     */
    public static function getSubFieldDataOfGivenField($field, $subFieldCode)
    {
        if ($field && $field instanceof \File_MARC_Data_Field) {

            $subField = $field->getSubfield($subFieldCode);

            return !empty($subField) ? htmlentities($subField->getData()) : false;
        }

        return false;
    }
}