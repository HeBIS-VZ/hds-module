<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 19.01.17
 * Time: 17:49
 */

namespace Hebis\View\Helper\Record\OtherEdition;


use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;

class OtherEditionTitleStatement extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /*
        WENN 245 $9 = patchF, DANN:
        490 $a_;_$v
        SONST:
        245 $a
        Enthält 245 $a eines der folgenden Sonderzeichen, dann vor diesem die Anzeige beenden: " / "  " = "  " : "
        */
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $_245 */
        $_245 = $marcRecord->getField(245);
        /** @var \File_MARC_Subfield $sf */
        if ($sf = $_245->getSubfields(9)) {
            if (strpos($sf->getData(),"patchF") !== false) {
                /** @var \File_MARC_Data_Field $_490 */
                $_490 = $marcRecord->getField(490);
                $_arr = [];
                $a = $_490->getSubfield('a');
                $v = $_490->getSubfield('v');

                !empty($a) ?: $_arr[] = $a->getData();
                !empty($v) ?: $_arr[] = $v->getData();

                return implode(" ; ", $_arr);
            }
        }

        /** @var \File_MARC_Subfield $_a */
        $a = $_245->getSubfield('a');

        if (!empty($a)) {
            $_a = $a->getData();
            for ($j = 0; $j < strlen($_a); ++$j) {
                if (in_array($_a{$j}, ['/', '=', ':'])) {
                    $i = $j - 1;
                    $k = $j + 1;
                    if ($i >= 0 && preg_match("/\s/",$_a{$i})
                        && $k <= strlen($_a) && preg_match("/\s/",$_a{$j})) {
                        return trim(substr($_a, 0, $i));
                    }
                }
            }
            return $_a;
        }

        return "";
    }
}