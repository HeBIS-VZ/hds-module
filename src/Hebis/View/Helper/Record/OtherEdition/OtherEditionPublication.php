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
        $out = "";
        /* WENN 264 Indikator 2 = 1, DANN anzeigen wie folgt:
        264 $a_:_$b,_$c

        Bei mehr als eine 264 mit Indikator 2 = 1 nur eine anzeigen; Priorisierung wie folgt:
        264 Indikator 1 = 3 und Indikator 2 = 1
        264 Indikator 1 = # und Indikator 2 = 1

        Kommen $a und/oder $b mehrfach vor, dann Trennzeichen: ";_" (in Worten: Semikolon Blank)*/

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $_264__ = $this->filterByIndicator($this->filterByIndicator($marcRecord->getFields('264'), 1, "3"), 2, "1");

        if (empty($_264__)) {
            $_264__ = $this->filterByIndicator($this->filterByIndicator($marcRecord->getFields('264'), 1, " "), 2, "1");
        }
        if (!empty($_264__)) {
            $out = $this->generateOutput(current($_264__));
        }

        return $out;
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @return string
     */
    private function generateOutput($field)
    {
        $a_ = $this->explode($field->getSubfields('a'), "; ");
        $b_ = $this->explode($field->getSubfields('b'), "; ");
        $c = $field->getSubfield('c');

        $ret = "";

        $ret .= !empty($a_) ? $a_ : "";
        $ret .= !empty($b_) ? " : " . $b_ : "";
        $ret .= !empty($c) ? ", " . $c->getData() : "";
        return $ret;

    }

    /**
     * @param array $subfields
     * @param string $delimiter
     * @return string
     */
    private function explode($subfields, $delimiter)
    {
        $ret = "";
        $n = count($subfields);
        for ($i = 0; $i < $n; ++$i) {
            /** @var \File_MARC_Subfield $subfield */
            $subfield = $subfields[0];
            $ret .= $subfield->getData();
            if ($i < $n-1) {
                $ret .= $delimiter;
            }
        }
        return trim($ret);
    }


}