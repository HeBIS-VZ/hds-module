<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 12.03.16
 * Time: 13:17
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordAdditionalPhysicalFormAvailableNote extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $arr = [];

        $fields = $marcRecord->getFields('530');

        if (($_6 = $marcRecord->getLeader()['6']) == "a" && ($_7 = $marcRecord->getLeader()['7']) == "s") {
            foreach ($fields as $field) {
                $arr[] = $this->getSubFieldDataOfGivenField($field, 'a');
            }
        }

        return implode("<br>\n", $arr);
    }
}