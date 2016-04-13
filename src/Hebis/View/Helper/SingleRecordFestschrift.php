<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.03.16
 * Time: 10:06
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordFestschrift extends SingleRecordAddedEntryPersonalName
{

    public function __invoke(SolrMarc $record)
    {
        /**
         * Here, the same rules apply as for SingleRecordAddedEntryPersonalName!
         * Just check if in field 700 subField $4 equals 'hnr'
         */

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $fields = $marcRecord->getFields('700');

        $arr = array_filter($fields, function(\File_MARC_Data_Field $field) {
            $subField = $field->getSubfield('4');
            return $subField->getData() === 'hnr'; //filter 'hnr' fields
        });

        return implode("; ", $this->extractContents($arr));
    }
}