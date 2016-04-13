<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 03.03.16
 * Time: 16:22
 */

namespace Hebis\View\Helper;

use Hebis\RecordDriver\SolrMarc;

class SingleRecordPublicationDistribution extends \Hebis\View\Helper\AbstractRecordViewHelper
{
    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $fields = $marcRecord->getFields('260');
        $fields_ = [];

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            if ($field->getIndicator(1) != "3") {
                array_push($fields_, $field);
            } else {
                array_unshift($fields_, $field);
            }
        }

        $arr = [];

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields_ as $field) {
            $ret = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $b = $this->getSubFieldDataOfGivenField($field, 'b');
            $c = $this->getSubFieldDataOfGivenField($field, 'c');
            $e = $this->getSubFieldDataOfGivenField($field, 'e');
            $f = $this->getSubFieldDataOfGivenField($field, 'f');

            $ret .= $a ? "$a : " : "";
            $ret .= $b ? "$b" : "";
            $ret .= $c ? ", $c." : "";

            if (($a || $b || $c) && $e) {
                $ret .= " - ($e";
                if ($f) {
                    $ret .= ", $f";
                }
                $ret .= ")";
                $ret = trim($ret) . ".";
            }
            $arr[] = trim($ret);
        }

        return implode("<br>\n", $arr);
    }

}