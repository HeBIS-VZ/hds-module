<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 02.03.16
 * Time: 21:12
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class RecordGetSubFieldDataOfField extends AbstractRecordViewHelper
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
        return $this->getSubFieldDataOfField($record, $fieldCode, $subFieldCode);
    }
}
