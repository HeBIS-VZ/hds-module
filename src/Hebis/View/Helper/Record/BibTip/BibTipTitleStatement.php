<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 20.01.17
 * Time: 14:45
 */

namespace Hebis\View\Helper\Record\BibTip;


use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\OtherEdition\OtherEditionTitleStatement;
use Hebis\View\Helper\Record\ResultList\ResultListTitleStatement;
use Hebis\View\Helper\Record\SingleRecord\SingleRecordTitleStatement;

class BibTipTitleStatement extends OtherEditionTitleStatement
{

    public function __invoke(SolrMarc $record)
    {
        /* WENN $n mit [...] besetzt, DANN $p anzeigen, SONST nur $n anzeigen
           Wiederholung von $n bzw. $p: "_;_" (Blank Semikolon Blank)
           Enthält ein Subfeld in 245 eines der folgenden Sonderzeichen, dann vor diesem die Anzeige beenden: " / "  " = "  " : "
           WENN 245 $9 = patchF, DANN:
           490 $a_;_$v
           SONST:
           245 $a_;_$n_;_$p */

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var \File_MARC_Data_Field $_245 */
        $_245 = $marcRecord->getField(245);
        /** @var \File_MARC_Subfield $sf */
        if ($sf = $_245->getSubfields(9)) {
            if (strpos($sf->getData(),"patchF") !== false) {
                return $this->extract490av($marcRecord);
            }
        }

        /* 245 $a_;_$n_;_$p */
        $_arr = [];
        $a = $this->flatten($_245, 'a');
        $n = $this->flatten($_245, 'n');
        $p = $this->flatten($_245, 'p');
        empty($a) ?: $_arr[] = $a;
        empty($n) ?: $_arr[] = $n;
        empty($p) ?: $_arr[] = $p;

        return implode(" ; ", $_arr);
    }

    private function flatten(\File_MARC_Data_Field $field, $subfieldCode)
    {
        $i = 0;
        $a = "";
        $subfields = $field->getSubfields($subfieldCode);
        /** @var \File_MARC_Subfield $_a */
        foreach ($subfields as $subfield) {
            $a .= $this->trimTitle($subfield);
            if ($i < count($subfields)-1) {
                $a .= " ; ";
            }
        }
        return $a;
    }

}