<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.17
 * Time: 10:05
 */

namespace Hebis\View\Helper\Record\OtherEdition;


use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;

class OtherEditionEditionStatement extends AbstractRecordViewHelper
{

    /**
     * @param SolrMarc $record
     * @return bool|string
     */
    public function __invoke(SolrMarc $record)
    {
        $_250a = $this->getSubFieldDataOfField($record, '250', 'a');

        return !empty($_250a) ? $_250a : "";
    }
}