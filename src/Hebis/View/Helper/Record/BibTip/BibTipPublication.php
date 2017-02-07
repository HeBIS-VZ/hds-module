<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.17
 * Time: 18:01
 */

namespace Hebis\View\Helper\Record\BibTip;


use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;

class BibTipPublication extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $_533_d = $this->getSubFieldDataOfField($record, 533, 'd');

        if (!empty($_533_d)) {
            return $_533_d;
        }

        $_264__ = $marcRecord->getFields(264);
        $_264_ = $this->filterByIndicator($_264__, 2, "1");

        usort($_264_, function (\File_MARC_Data_Field $a, \File_MARC_Data_Field $b) {
            return $a->getIndicator(1) > $b->getIndicator(1) ? -1 : 1;
        });

        if (!empty($_264_)) {
            $a = current($_264_);
            if (!empty($c = $a->getSubfield('c'))) {
                return $c->getData();
            }
        }

        return "";
    }
}