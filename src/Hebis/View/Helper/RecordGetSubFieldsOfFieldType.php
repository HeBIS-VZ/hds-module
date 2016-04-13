<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 12.03.16
 * Time: 20:50
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class RecordGetSubFieldsOfFieldType extends AbstractRecordViewHelper
{

    /**
     * returns the data of the subField if field and subField exists, otherwise false
     *
     * @param SolrMarc $record
     * @param $fieldCode
     * @param $subFieldCode
     * @return bool|string
     */
    public function __invoke(SolrMarc $record, $fieldCode, $subFieldCode)
    {
        return $this->getSubFieldsOfFieldType($record, $fieldCode, $subFieldCode);
    }
}
