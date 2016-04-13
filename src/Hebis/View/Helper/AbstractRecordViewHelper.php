<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 02.03.16
 * Time: 17:30
 */

namespace Hebis\View\Helper;


use \File_MARC_Data_Field;
use Hebis\RecordDriver\SolrMarc;
use Zend\View\Helper\AbstractHelper;

class AbstractRecordViewHelper extends AbstractHelper
{
    use FieldArray;

    /** Search Link Pattern Full Title */
    const URL_FULL_TITLE_SEARCH_PATTERN = 'Search/Results?lookfor0[]=%s&type0[]=fulltitle';


    const URL_AUTHOR_SEARCH_PATTERN = 'Search/Results?lookfor0[]=&type0[]=author&lastposition';


    /**
     * checks if subField exists, if true it returns the subField containing data
     * otherwise it returns an empty string
     *
     * @param File_MARC_Data_Field $field
     * @param string $subFieldCode
     * @return string
     */
    protected function getSubField(File_MARC_Data_Field $field, $subFieldCode)
    {
        return !$field->getSubfield($subFieldCode) ? '' : trim($field->getSubfield($subFieldCode)->getData());
    }

    /**
     * returns the data of the subField if field and subField exists, otherwise false
     *
     * @param SolrMarc $record
     * @param $fieldCode
     * @param $subFieldCode
     * @return bool|string
     */
    protected function getSubFieldDataOfField(SolrMarc $record, $fieldCode, $subFieldCode)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $field */
        $field = $marcRecord->getField($fieldCode);

        return $this->getSubFieldDataOfGivenField($field, $subFieldCode);
    }

    /**
     * if field of type \File_MARC_Data_Field and it has a subField with $subFieldCode this function returns the data
     * string of the subField
     *
     * @param \File_MARC_Data_Field|bool $field
     * @param $subFieldCode
     * @return bool|string
     */
    protected function getSubFieldDataOfGivenField($field, $subFieldCode)
    {

        if ($field && $field instanceof \File_MARC_Data_Field) {

            /** @var \File_MARC_Subfield $subField */
            $subField = $field->getSubfield($subFieldCode);

            if ($subField) {
                return $subField->getData();
            }
        }

        return false;
    }

    /**
     * if $needle is a substring at the beginning of $haystack this function returns the haystack truncated to $needle,
     * otherwise it returns the full haystack
     *
     * @param string $haystack
     * @param string $needle
     * @return string
     */
    protected function removePrefix($haystack, $needle)
    {
        if (strpos($haystack, $needle) === 0) {
            return substr($haystack, strlen($needle));
        }
    }

    /**
     * Returns an array containing sub field data of $subFieldCode from all fields of $fieldCode comprised in given
     * $record
     *
     * @param SolrMarc $record
     * @param string $fieldCode
     * @param string $subFieldCode
     * @return array
     */
    protected function getSubFieldsOfFieldType(SolrMarc $record, $fieldCode, $subFieldCode)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $fields = $marcRecord->getFields($fieldCode);
        $arr = [];

        foreach ($fields as $field) {
            $subField = $this->getSubFieldDataOfGivenField($field, $subFieldCode);
            if ($subField) {
                $arr[] = $subField;
            }
        }
        return $arr;
    }

    /**
     *
     * Generates Link including basePath
     *
     * @param string $href
     * @param string $title
     * @param string $linkText
     * @return string
     */
    protected function generateLink($href, $title, $linkText)
    {
        $href = $this->getView()->basePath() . "/" . $href;
        return sprintf('<a href="%s" title="%s">%s</a>', $href, htmlentities($title), htmlentities($linkText));
    }
}