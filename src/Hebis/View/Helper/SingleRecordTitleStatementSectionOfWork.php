<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.03.16
 * Time: 10:00
 */

namespace Hebis\View\Helper;

use \File_MARC_Record;
use Hebis\RecordDriver\SolrMarc;
use Zend\View\Helper\AbstractHelper;

class SingleRecordTitleStatementSectionOfWork extends SingleRecordTitleStatement
{
    public function __invoke(SolrMarc $record)
    {
        /** @var File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $arr = [];

        if ($marcRecord->getLeader()['19'] == " ") {
            /** @var \File_MARC_Data_Field $field */
            $field = $marcRecord->getField('245');
            $ret = "";

            /** @var \File_MARC_Subfield $subField */
            foreach ($field->getSubfields() as $subField) {

                switch ($subField->getCode()) {
                    case 'n':
                        if (!preg_match('/^\[^\]\]$/', trim($subField->getData()))) {
                            $ret .= $subField->getData();
                        }
                        break;
                    case 'p':
                        $ret .= $subField ? ". ".$subField->getData() : "";
                        if (!empty($ret)) {
                            $arr[] = $ret;
                            $ret = "";
                        }
                        break;
                }
            }
        }

        return implode("<br>\n", $arr);
    }
}