<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 12.03.16
 * Time: 13:46
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordInternationalStandardSerialNumber extends AbstractRecordViewHelper
{
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $arr = [];

        $fields = $marcRecord->getFields('022');

        foreach ($fields as $field) {

            $subFields = $field->getSubfields();
            foreach ($subFields as $subField) {
                switch ($subField->getCode()) {
                    case 'a':
                    case 'y':
                        $arr[] = htmlentities($subField->getData());
                        break;
                }
            }
        }

        $fields = $marcRecord->getFields('029');

        foreach ($fields as $field) {
            $str = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $str .= $a ? htmlentities($a) : "";

            $arr[] = $str;
        }

        return implode(" ; ", $arr);
    }
}