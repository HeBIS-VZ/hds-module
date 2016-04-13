<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.03.16
 * Time: 10:00
 */

namespace Hebis\View\Helper;

use File_MARC_Record;

use Hebis\RecordDriver\SolrMarc;
use Zend\View\Helper\AbstractHelper;

/**
 *
 * @package Hebis\View\Helper
 */
class SingleRecordUniformTitle extends AbstractRecordViewHelper
{
    use FieldArray;

    public function __invoke(SolrMarc $record)
    {
        /** @var File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $ret = "";
        $fields = [];

        foreach (['240', '243', '730'] as $fieldCode) {
            $fields[] = $marcRecord->getField($fieldCode);
        }

        $a = $g = $r = false;
        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            if ($field) {
                $a = $this->getSubFieldDataOfGivenField($field, 'a');
                $g = $this->getSubFieldDataOfGivenField($field, 'g');
                $r = $this->getSubFieldDataOfGivenField($field, 'r');
                $ret .= $a ? $a : "";
                $ret .= $g ? " <$g>" : "";
                $ret .= $r ? " <$r>" : "";
                $ret .= "<br />\n"; //newline
                $a = $g = $r = false;
            }
        }

        return $ret;
    }

    /**
     * removes @ at the beginning of the string or an @ where a blank as prefix exist and followed by a word a digit
     * @param $string
     * @return mixed
     */
    public function removeSpecialChars($string)
    {
        $string = preg_replace('/^@/', "", $string);
        return preg_replace('/\s\@([\w\däöü])/', " $1", $string);
    }
}