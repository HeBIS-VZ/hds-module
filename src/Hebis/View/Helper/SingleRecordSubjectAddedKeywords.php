<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 08.03.16
 * Time: 11:41
 */

namespace Hebis\View\Helper;
use \File_MARC_Record;

use Hebis\RecordDriver\SolrMarc;

class SingleRecordSubjectAddedKeywords extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /** @var File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $dataFields = array_merge(
            $this->append600($marcRecord),
            $this->append610($marcRecord),
            $this->append611($marcRecord),
            $this->append630($marcRecord),
            $this->append650($marcRecord),
            $this->append651($marcRecord),
            $this->append655($marcRecord),
            $this->append648($marcRecord)
        );

        array_filter($dataFields, 'strlen');

        return implode(" ; ", $dataFields); //$this->removeLastSemicolon($ret);
    }

    private function append600(\File_MARC_Record $marcRecord)
    {
        $arr = [];
        $fields = $marcRecord->getFields('600');

        foreach ($fields as $field) {
            $str = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $b = $this->getSubFieldDataOfGivenField($field, 'b');
            $c = $this->getSubFieldDataOfGivenField($field, 'c');
            $t = $this->getSubFieldDataOfGivenField($field, 't');
            $x = $this->getSubFieldDataOfGivenField($field, 'x');

            $str .= $a ? $a : "";
            $str .= $b ? " $b" : "";
            $str .= $c ? " &lt;$c&gt;" : "";

            if ($t || $x) {
                $str .= " / ";
                if ($t && $x) {
                    $str .= "$t, $x";
                } else {
                    $str .= $t ? $t : "";
                    $str .= $x ? $x : "";
                }
            }
            $arr[] = $str;
        }

        return $arr; //$this->appendSemicolon($ret);
    }

    private function append610(File_MARC_Record $marcRecord)
    {
        $arr = [];

        $fields = $marcRecord->getFields('610');

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {

            $str = "";
            $sf = $field->getSubfields();

            /** @var \File_MARC_Subfield $item */
            foreach ($sf as $item) {
                switch ($item->getCode()) {
                    case 'a':
                        $str .= $item ? $item->getData() : "";
                        break;
                    case 'b':
                        $str .= $item ? " / ".$item->getData() : "";
                        break;
                    case 'g':
                        $str .= $item ? " &lt;".$item->getData()."&gt;" : "";
                        break;
                    case 't':
                        $str .= " / ";
                        $str .= $item ? $item->getData() : "";
                        break;
                    case 'x':
                        $str .= ", ".$item->getData();
                }
            }

            $arr[] = $str;
        }

        return $arr; //$this->appendSemicolon($ret);
    }

    /**
     * @param File_MARC_Record $marcRecord
     * @return array
     */
    private function append611(File_MARC_Record $marcRecord)
    {
        $arr = [];
        $fields = $marcRecord->getFields('611');

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {

            $str = "";
            $sf = $field->getSubfields();

            /** @var \File_MARC_Subfield $item */
            foreach ($sf as $item) {
                switch ($item->getCode()) {
                    case 'a':
                        $str .= $item ? $item->getData() : "";
                        break;
                    case 'c':
                    case 'd':
                    case 'f':
                    case 'n':
                    case 'x':
                        $str .= $item ? ", ".$item->getData() : "";
                        break;
                    case 'e':
                    case 't':
                        $str .= $item ? " / ".$item->getData() : "";
                        break;
                    case 'g':
                        $str .= " / ";
                        $str .= $item ? " &lt;".$item->getData()."&gt;" : "";
                        break;
                    case 'x':
                        $str .= ", ".$item->getData();
                }
            }
            $arr[] = $str;
        }

        return $arr; //$this->appendSemicolon($ret);
    }

    private function append630(File_MARC_Record $marcRecord)
    {
        $arr = [];
        $fields = $marcRecord->getFields('630');

        foreach ($fields as $field) {
            $str = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $d = $this->getSubFieldDataOfGivenField($field, 'd');
            $e = $this->getSubFieldDataOfGivenField($field, 'e');
            $f = $this->getSubFieldDataOfGivenField($field, 'f');
            $g = $this->getSubFieldDataOfGivenField($field, 'g');
            $n = $this->getSubFieldDataOfGivenField($field, 'n');
            $t = $this->getSubFieldDataOfGivenField($field, 't');
            $x = $this->getSubFieldDataOfGivenField($field, 'x');

            $str .= $a ? $a : "";
            $str .= $d ? ", $d" : "";
            $str .= $e ? ", $e" : "";
            $str .= $f ? ", $f" : "";
            $str .= $g ? "&lt;$g&gt;" : "";
            $str .= $n ? ", $n" : "";

            if ($t || $x) {
                $str .= " / ";
                if ($t && $x) {
                    $str .= "$t, $x";
                } else {
                    $str .= $t ? $t : "";
                    $str .= $x ? $x : "";
                }
            }
            $arr[] = $str;
        }

        return $arr; //$this->appendSemicolon($ret);
    }

    private function append650(\File_MARC_Record $marcRecord)
    {
        $arr = [];

        $fields = $marcRecord->getFields('650');

        foreach ($fields as $field) {
            $str = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $c = $this->getSubFieldDataOfGivenField($field, 'c');
            $x = $this->getSubFieldDataOfGivenField($field, 'x');
            $_9 = $this->getSubFieldDataOfGivenField($field, '9');

            $str .= $a ? $a : "";
            $str .= $c ? " &lt;$c&gt;" : "";
            $str .= $x ? ", $x" : "";
            $str .= $_9 ? " &lt;".preg_replace("/^[a-z]{1}:/","",$_9)."&gt;" : "";
            $arr[] = $str;
        }

        return $arr; //$this->appendSemicolon($ret);
    }

    private function append651($marcRecord)
    {
        $arr = [];

        $fields = $marcRecord->getFields('651');

        foreach ($fields as $field) {
            $str = "";
            $sf = $field->getSubfields();

            /** @var \File_MARC_Subfield $item */
            foreach ($sf as $item) {
                switch ($item->getCode()) {
                    case 'a':
                        $str .= $item ? htmlentities($item->getData()) : "";
                        break;
                    case 'g':
                    case 'z':
                        $str .= $item ? ", ".$item->getData() : "";
                        break;
                    case 'x':
                        $str .= $item ? " / ".$item->getData() : "";
                        break;
                }
            }
            $arr[] = $str;
        }

        return $arr; //$this->appendSemicolon($ret);

    }

    private function append655($marcRecord)
    {
        $arr = [];
        $fields = $marcRecord->getFields('655');

        foreach ($fields as $field) {
            $str = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $z = $this->getSubFieldDataOfGivenField($field, 'z');

            $str .= $a ? $a : "";
            $str .= $z ? " $z" : "";
            $arr[] = $str;
        }

        return $arr; //$this->appendSemicolon($ret);
    }

    private function append648(File_MARC_Record $marcRecord)
    {
        $a = $this->getSubFieldDataOfGivenField($marcRecord->getField(648), 'a');
        $ret = $a ? $a : "";
        return $ret ? [$ret] : []; //$this->appendSemicolon($ret);
    }

    private function appendSemicolon($ret)
    {
        return !empty($ret) ? $ret." ; " : "";
    }

    private function removeLastSemicolon($ret)
    {
        preg_replace("/;$/", "", $ret);
    }


}