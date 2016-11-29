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
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;


/**
 * Class SingleRecordSeriesStatementAddedEntry
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordSeriesStatementAddedEntry extends AbstractRecordViewHelper
{
    /**
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /* Wenn  Leader Pos. 19 = c:
        245 $a_:_$b_/_$c
        + Zusatzregeln (s. Anmerkungen) */
        $str = "";
        $leader = $marcRecord->getLeader();
        if (substr($leader, 19, 1) === "c") {
            $fields = $marcRecord->getFields(245);
            foreach ($fields as $field) {
                $str .= implode(" : ", $this->getSubFieldsDataArrayOfField($field, ['a', 'b']));
                if (!empty($c = $this->getSubFieldDataOfGivenField($field, 'c'))) {
                    $str .= " / $c";
                }
            }
        }


        /* Wenn Leader Pos. 19 = b oder c:
        800 $a_$b,_$c:_$t_:_$n,_$p_;_$v
        810 $a._$b_($g)_($n):_$t_:_$n,_$p_;_$v
        811 $a_($g)_($n_:_$c_:_$d):_$t_:_$n,_$p_;_$v
        830 $a_:_$n,_$p_;_$v
        + Zusatzregeln (s. Anmerkungen) */

        if (substr($leader, 19, 1) === "c" || substr($leader, 19, 1) === "b") {
            $fields = $marcRecord->getFields(800);
            foreach ($fields as $field) {
                $str .= $this->generate800($field);
            }

            $fields = $marcRecord->getFields(810);
            foreach ($fields as $field) {
                $str .= "\n".$this->generate810($field);
            }

            $fields = $marcRecord->getFields(811);
            foreach ($fields as $field) {
                $str .= "\n".$this->generate811($field);
            }

            $fields = $marcRecord->getFields(830);
            foreach ($fields as $field) {
                $str .= "\n".$this->generate830($field);
            }
        }
        /*
        Wenn Leader Pos. 19 = # oder a:
        490 $a,_$x_;_$v
        "ISSN_" vor $x ergänzen */
        if (substr($leader, 19, 1) === " " || substr($leader, 19, 1) === "a") {
            $fields = $marcRecord->getFields(490);
            $str .= "\n";
            foreach ($fields as $field) {
                $str .= implode(" , ", $this->getSubFieldsDataArrayOfField($field, ['a', 'x']));
                if (!empty($v = $this->getSubFieldDataOfGivenField($field, 'v'))) {
                    $str .= " ; ISSN $v";
                }
            }
        }

        return $str;
    }


    private function generate800($field)
    {
        $ret = "";
        //800 $a_$b,_$c:_$t_:_$n,_$p_;_$v
        $ret .= implode(" ", $this->getSubFieldsDataArrayOfField($field, ['a', 'x']));

        $c_t = implode(": ", $this->getSubFieldsDataArrayOfField($field, ['c', 't']));
        $ret .= (!empty($c_t)) ? ", $c_t" : "";

        $ret .= !empty($n = $this->getSubFieldDataOfGivenField($field, 'n')) && strpos($n, "[...]") !== false ? " : $n" : "";
        $ret .= !empty($p = $this->getSubFieldDataOfGivenField($field, 'p')) ? ", $p" : "";


        $v = $this->getSubFieldDataOfGivenField($field, 'v');
        $ret .= (!empty($v)) ? " ; $v" : "";

        return $ret;
    }

    private function generate810($field)
    {
        //810 $a._$b_($g)_($n):_$t_:_$n,_$p_;_$v

        $ret = "";
        //800 $a_$b,_$c:_$t_:_$n,_$p_;_$v
        $ret .= !empty($a = $this->getSubFieldDataOfGivenField($field, 'a')) ? "$a" : "";
        $ret .= !empty($b = $this->getSubFieldDataOfGivenField($field, 'b')) ? ". $b" : "";
        $ret .= !empty($g = $this->getSubFieldDataOfGivenField($field, 'g')) ? " ($g)" : "";
        $ret .= !empty($n = $this->getSubFieldDataOfGivenField($field, 'n')) && strpos($n, "[...]") !== false ? " ($n)" : "";

        $ret .= ": ".implode(" : ", $this->getSubFieldsDataArrayOfField($field, ['t', 'n']));

        $ret .= !empty($p = $this->getSubFieldDataOfGivenField($field, 'p')) ? ", $p" : "";
        $ret .= !empty($v = $this->getSubFieldDataOfGivenField($field, 'v')) ? " ; $v" : "";

        return $ret;
    }

    private function generate811($field)
    {
        //811 $a_($g)_($n_:_$c_:_$d):_$t_:_$n,_$p_;_$v
        $ret = "";

        $ret .= !empty($a = $this->getSubFieldDataOfGivenField($field, 'a')) ? "$a" : "";
        $ret .= !empty($g = $this->getSubFieldDataOfGivenField($field, 'g')) ? " ($g)" : "";

        $cd = $this->getSubFieldsDataArrayOfField($field, ['c', 'd']);
        !empty($n = $this->getSubFieldDataOfGivenField($field, 'n')) && strpos($n, "[...]") === false ? $ncd = array_merge([$n], $cd) : $ncd = $cd;

        $n_c_d = "(".implode(" : ", $ncd).")";
        $ret .= !empty($n_c_d) ? " $n_c_d" : "";

        $t_n = implode(" : ", $this->getSubFieldsDataArrayOfField($field, ['t', 'n']));
        $ret .= !empty($t_n) ? ": $t_n" : "";

        $ret .= !empty($p = $this->getSubFieldDataOfGivenField($field, 'p')) ? ", $p" : "";
        $ret .= !empty($v = $this->getSubFieldDataOfGivenField($field, 'v')) ? " ; $v" : "";

        return $ret;
    }

    private function generate830($field)
    {
        // 830 $a_:_$n,_$p_;_$v
        $ret = "";
        $ret .= implode(" : ", $this->getSubFieldsDataArrayOfField($field, ['a', 'n']));

        $ret .= !empty($p = $this->getSubFieldDataOfGivenField($field, 'p')) ? ", $p" : "";
        $ret .= !empty($v = $this->getSubFieldDataOfGivenField($field, 'v')) ? " ; $v" : "";

        return $ret;
    }
}