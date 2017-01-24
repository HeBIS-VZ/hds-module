<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.17
 * Time: 16:55
 */

namespace Hebis\View\Helper\Record\BibTip;


use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\CorporateHelperTrait;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordCorporateName;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordPersonalName;

class BibTipPersonalName extends SingleRecordPersonalName
{

    public function __invoke(SolrMarc $record, $test = true)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        $field100 = $marcRecord->getField('100');

        $aut = $this->getFieldContents($field100);

        if (!empty($aut)) {
            return $aut;
        }

        $aut = "";

        $f700_ = $marcRecord->getFields(700);

        if (!empty($f700_)) {
            $filteredFields = $this->filterByIndicator($f700_, 2, " ");
            $aut = $this->getFieldContents($filteredFields[0]);
        }

        if (!empty($aut)) {
            return $aut;
        }

        $arr[] = $this->getView()->singleRecordCorporateName($record, true);

        return $arr[0];
    }

}