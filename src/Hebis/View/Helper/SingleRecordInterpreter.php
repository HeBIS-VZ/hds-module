<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.03.16
 * Time: 10:07
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordInterpreter extends SingleRecordAddedEntryPersonalName
{
    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $fields = array_merge(
            $marcRecord->getFields('700'),
            $marcRecord->getFields('710')
        );

        $fields_ = array_filter($fields, function(\File_MARC_Data_Field $field) {
            $subField = $field->getSubfield('4');
            return !empty($subField) && in_array($subField->getData(), ['prf', 'mus']);
        });

        $arr = [];

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields_ as $field) {
            $ret = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $b = $this->getSubFieldDataOfGivenField($field, 'b');


            switch($field->getTag()) {
                case '700':
                    $c = $this->getSubFieldDataOfGivenField($field, 'c');
                    $e = $this->getSubFieldDataOfGivenField($field, 'e');
                    $ret .= $a ? $a : "";
                    $ret .= $b ? " $b" : "";
                    $ret .= $c ? " <$c>" : "";
                    $ret .= $e ? " ($e)" : "";
                    break;
                case '710':
                    $g = $this->getSubFieldDataOfGivenField($field, 'g');
                    $n = $this->getSubFieldDataOfGivenField($field, 'n');
                    $ret .= $a ? $a : "";
                    $ret .= $b ? " / $b" : "";
                    $ret .= $g ? " <$g>" : "";
                    $ret .= $n ? " <$n>" : "";
                    break;
            }

            $arr[] = $this->authorSearchLink($ret);
        }


        return implode("<br>\n", $arr);
    }


}