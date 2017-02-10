<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.02.17
 * Time: 13:41
 */

namespace Hebis\Csl\MarcConverter;


trait SubfieldsTrait
{

    /**
     * @param \File_MARC_Record $record
     * @param string $fieldCode
     * @param string $subFieldCode
     * @param \Closure $filterCallback
     * @return null|string
     */
    protected static function getSubfield($record, $fieldCode, $subFieldCode, $filterCallback = null)
    {
        $ret = null;
        /** @var \File_MARC_Data_Field $field */
        $field = null;
        if (!empty($filterCallback)) {
            $fields = array_filter($record->getFields($fieldCode), $filterCallback);
            $field = count($fields) > 0 ? $fields[0] : null;
        } else {
            $field = $record->getField($fieldCode);
        }

        if (!empty($field)) {
            $subfield = $field->getSubfield($subFieldCode);

            if (!empty($subfield)) {
                $ret = $subfield->getData();
            }
        }
        return $ret;
    }
}