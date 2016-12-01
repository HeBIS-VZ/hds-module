<?php

/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an
 * extension of the open source library search engine VuFind, that
 * allows users to search and browse beyond resources. More
 * Information about VuFind you will find on http://www.vufind.org
 *
 * Copyright (C) 2016
 * HeBIS Verbundzentrale des HeBIS-Verbundes
 * Goethe-Universität Frankfurt / Goethe University of Frankfurt
 * http://www.hebis.de
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Hebis\View\Helper\Record\SingleRecord;

use File_MARC_Record;

use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use Zend\View\Helper\AbstractHelper;

/**
 * Class SingleRecordUniformTitle
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordUniformTitle extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /** @var File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $ret = [];
        $fields = [];
        /*
        Detailanzeige:
        240 $a <$g>
Wenn 240 + 040 $e = rda, dann:
240 $a_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
130 $a <$g>
Wenn 130 + 040 $e = rda, dann:
130 $a_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
243 $a <$g>
730 $a <$g>
Wenn 730 + 040 $e = rda, dann:
730 $a_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
Wenn 700, 710, 711 Indikator 2 = 2:
700 $t_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
710 $t_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
711 $t_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s

        -> Reihenfolge der Subfelder wie im Datensatz hinterlegt
*/



        foreach (['040', '130', '240', '243', '700', '710', '711', '730'] as $fieldCode) {
            $field = $marcRecord->getFields($fieldCode);
            if (!empty($field)) {
                if (count($field) === 1) {
                    $field = $field[0];
                }

                $fields[$fieldCode] = $field;
            }
        }

        if (array_key_exists('240', $fields)) {
            $ret[] = $this->generateAG($fields['240']);
        }

        //Wenn 240 + 040 $e = rda, dann:
        //240 $a_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
        //130 $a <$g>
        if (array_key_exists('040', $fields) && array_key_exists('240', $fields)
            && strpos($this->getSubFieldDataOfGivenField($fields['040'], "e"), "rda") !== false
            && strpos($this->getSubFieldDataOfGivenField($fields['240'], "e"), "rda") !== false) {
            $ret[] = $this->generateContent($fields['240']);

            if (array_key_exists('130', $fields)) {
                $ret[] = $this->generateAG($fields['130']);
            }
        }

        //Wenn 130 + 040 $e = rda, dann:
        //130 $a_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
        //243 $a <$g>
        //730 $a <$g>
        if (array_key_exists('040', $fields) && array_key_exists('130', $fields)
            && strpos($this->getSubFieldDataOfGivenField($fields['040'], 'e'), "rda") !== false
            && strpos($this->getSubFieldDataOfGivenField($fields['130'], "e"), "rda") !== false) {
            $ret[] = $this->generateContent($fields['130']);
            if (array_key_exists('243', $fields)) {
                $ret[] = $this->generateAG($fields['243']);
            }
            if (array_key_exists('730', $fields)) {
                $ret[] = $this->generateAG($fields['730']);
            }
        }


        //Wenn 730 + 040 $e = rda, dann:
        //730 $a_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
        if (array_key_exists('040', $fields) && array_key_exists('730', $fields)
            && strpos($this->getSubFieldDataOfGivenField($fields['040'], "e"), "rda") !== false) {

            if (is_array($fields['730']) || $fields['730'] instanceof \File_MARC_List) {
                foreach ($fields['730'] as $field) {
                    if (strpos($this->getSubFieldDataOfGivenField($field, "e"), "rda") !== false) {
                        $ret[] = $this->generateContent($field);
                    }
                }
            } else {
                if (strpos($this->getSubFieldDataOfGivenField($fields['730'], "e"), "rda") !== false) {
                    $ret[] = $this->generateContent($fields['730']);
                }
            }
        }

        //Wenn 700, 710, 711 Indikator 2 = 2:
        //700 $t_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
        //710 $t_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
        //711 $t_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
        if (array_key_exists('700', $fields)) {
            if (is_array($fields['700']) || $fields['700'] instanceof \File_MARC_List) {
                foreach ($fields['700'] as $field) {
                    if ($field->getIndicator(2) == "2") {
                        $ret[] = $this->generateContent($field);
                    }
                }
            } else {
                if ($fields['700']->getIndicator(2) == "2") {
                    $ret[] = $this->generateContent($fields['700']);
                }
            }

        }

        if (array_key_exists('710', $fields)) {
            if (is_array($fields['710']) || $fields['710'] instanceof \File_MARC_List) {
                foreach ($fields['710'] as $field) {
                    if ($field->getIndicator(2) == "2") {
                        $ret[] = $this->generateContent($field);
                    }
                }
            } else {
                if ($fields['710']->getIndicator(2) == "2") {
                    $ret[] = $this->generateContent($fields['710']);
                }
            }
        }

        if (array_key_exists('711', $fields)) {
            if (is_array($fields['711']) || $fields['711'] instanceof \File_MARC_List) {
                foreach ($fields['711'] as $field) {
                    if ($field->getIndicator(2) == "2") {
                        $ret[] = $this->generateContent($field);
                    }
                }
            } else {
                if ($fields['711']->getIndicator(2) == "2") {
                    $ret[] = $this->generateContent($fields['711']);
                }
            }
        }

        return implode("<br />", $ret);
    }

    /**
     * removes @ at the beginning of the string or an @ where a blank as prefix exist and followed by a word a digit
     * @param $string
     * @return mixed
     */
    public function removeSpecialChars($string)
    {
        $string = preg_replace('/^@/', "", $string);
        return preg_replace('/\s\@([\w\däöü])/', " $1", $string);
    }

    private function generateContent($field)
    {
        //$a_($f)_($g)._$k,_$m,_$n;_$o._$p,_$r._$s
        $str = "";
        $subFields = $this->getSubFieldsDataArrayOfField($field, ['a', 'f', 'g', 'k', 'm', 'n', 'o', 'p', 'r', 's']);

        foreach ($subFields as $code => $subField)
        {
            switch ($code) {
                case "a":
                    $str .= $subField;
                    break;
                case "f":
                case "g":
                    $str .= " ($subField)";
                    break;
                case "k":
                case "p":
                case "s":
                    $str .= ". $subField";
                    break;
                case "m":
                case "n":
                case "r":
                    $str .= ", $subField";
                    break;
                case "o":
                    $str .= "; $subField";
                default:
            }
        }

        return $str;
    }

    private function generateAG($field)
    {
        $ret = "";
        $a = $this->getSubFieldDataOfGivenField($field, 'a');
        $g = $this->getSubFieldDataOfGivenField($field, 'g');

        $ret .= !empty($a) ? $a : "";
        $ret .= !empty($g) ? " &lt;$g&gt;" : "";

        return $ret;
    }
}