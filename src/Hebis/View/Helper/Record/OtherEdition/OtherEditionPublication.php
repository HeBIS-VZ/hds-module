<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.17
 * Time: 10:12
 */

namespace Hebis\View\Helper\Record\OtherEdition;

use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordPublication;

class OtherEditionPublication extends SingleRecordPublication
{

    public function __invoke(SolrMarc $record, $asArray = false)
    {
        $arr = parent::__invoke($record, true);
        return $arr[0];
    }
}