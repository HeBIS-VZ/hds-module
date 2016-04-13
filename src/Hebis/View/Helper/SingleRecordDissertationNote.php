<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 11.03.16
 * Time: 11:40
 */

namespace Hebis\View\Helper;


use \File_MARC_Data_Field;
use \File_MARC_Record;
use Hebis\RecordDriver\SolrMarc;

class SingleRecordDissertationNote extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        $ret = "";

        /** @var File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_List $field */
        $fields = $marcRecord->getFields('502');

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $ret .= $a ? $a : "";
        }

        return $ret;
    }


}