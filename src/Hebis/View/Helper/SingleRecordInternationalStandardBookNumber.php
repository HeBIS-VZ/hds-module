<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 12.03.16
 * Time: 13:46
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordInternationalStandardBookNumber extends AbstractRecordViewHelper
{
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $arr = [];

        $fields = $marcRecord->getFields('020');

        foreach ($fields as $field) {
            $str = "";
            $_9 = $this->getSubFieldDataOfGivenField($field, '9');
            $z = $this->getSubFieldDataOfGivenField($field, 'z');
            $a = $this->getSubFieldDataOfGivenField($field, 'a');

            if ($_9) {
                $str = $_9 ? $_9 : "";
            }
            else if ($z) {
                $str = $z ? $z : "";
            }

            if (is_string($a) && strpos($a, "(Sekundärausgabe)") !== false) {
                $str .= " (Sekundärausgabe)";
            }

            $arr[] = $str;
        }

        return implode("<br>\n", $arr);
    }
}