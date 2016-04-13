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
class SingleRecordOtherEditionEntry extends AbstractRecordViewHelper
{
    use FieldArray;

    public function __invoke(SolrMarc $record)
    {
        /** @var File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $ret = "";

        $fields = $marcRecord->getFields('775');



        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            if ($field) {
                $i = $this->getSubFieldDataOfGivenField($field, 'i');
                $a = $this->getSubFieldDataOfGivenField($field, 'a');
                $t = $this->getSubFieldDataOfGivenField($field, 't');

                $ret .= $i ? "$i: " : "";

                if ($a || $t) {
                    $w = $this->getSubFieldDataOfGivenField($field, 'w');
                    if ($a && $t) {
                        $ret .= '<a href="'.$this->link($w).'">'."$a: $t".'</a>';
                    } else {

                        $ret .= $a ? '<a href="'.$this->link($w).'">'.$a.'</a>' : "";
                        $ret .= $t ? '<a href="'.$this->link($w).'">'.$t.'</a>' : "";
                    }
                }

                $ret .= "<br />\n"; //newline
            }
        }

        return $ret;
    }

    private function link($w) {
        return $this->getView()->basePath().'/Search/Results?lookfor0[]=HEB'.$this->removePrefix($w, "(DE-603)").'&type0[]=isn';
    }
}