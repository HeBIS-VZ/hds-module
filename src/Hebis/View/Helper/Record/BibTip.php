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
namespace Hebis\View\Helper\Record;

use HAB\Pica\Record\Field;
use HAB\Pica\Record\SubField;
use Hebis\RecordDriver\SolrMarc;

/**
 * Class BibTip
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class BibTip extends AbstractRecordViewHelper
{
    public function __invoke(SolrMarc $record)
    {


        $ret = "";


        $isbn = $record->getCleanISBN();
        $issn = $isbn ? $isbn : $record->getCleanISSN();

        //$ret .= '<div id="bibtip_isxn" style="display:none">'.!empty($isbn) ? $isbn : $issn.'</div>';
        //$ret .= '<div id="bibtip_shorttitle">';

        $shortTitle = preg_split('/[:\/,=]/', $record->getShortTitle())[0];
        $shortTitle = trim($shortTitle);
        $marcRecord = $record->getMarcRecord();
        $picaRecord = $record->getPicaRecord();

        $year = "";

        if (!empty($picaRecord->getFields('011@'))) {
            /** @var Field $p_011at Pica Field 011@ */
            $p_011at = $picaRecord->getFields('011@')[0];
            $year = $this->buildPublishDate($p_011at);
        }


        //Author
        /** @var Field $p_028a */
        $p_028a = $p_028c = $p_029a = null;

        $author = "";

        if (!empty($picaRecord->getFields('028A'))) {
            $p_028a = $picaRecord->getFields('028A')[0];
        }
        if (!empty($picaRecord->getFields('028C'))) {
            $p_028c = $picaRecord->getFields('028C')[0];
        }

        if (!empty($picaRecord->getFields('029A'))) {
            $p_029a = $picaRecord->getFields('029A')[0];
        }

        foreach([$p_029a, $p_028c, $p_028a] as $field) {
            if (empty($field)) {
                continue;
            }
            $author = $this->buildAuthorString($field);
            break;
        }

        $ret .=  "$shortTitle / $author $year";


        //TODO: Koerperschaft
        /*
        if (!$bibtipauthor && !$bibtipsecauthor && $bibtipkoerperschaft) {
            $ret .= "/" . htmlspecialchars(str_replace("{", "", $bibtipkoerperschaft));
        }
        */

        //$bibtipId = $record->extractPPN();
        //$ret .= "</div>";
        //$ret .= "<div id=\"bibtip_id\" style=\"display:none\">".$record->getPPN()."</div>";
        //$ret .= "<script src=\"https://recommender.bibtip.de/js/bibtip_ub_f_portal.js\" type=\"text/javascript\"></script>";
        //$ret .= "<div id=\"bibtip_reclist\" style=\"display:none\"></div>";

        return $ret;
    }

    /**
     * @param Field $field
     * @return string
     */
    private function buildAuthorString(Field $field)
    {
        $_5 = $field->hasSubFields('5') ? $field->getNthSubField('5', 0)->getValue() : "";
        $_8 = $field->hasSubFields('8') ? $field->getNthSubField('8', 0)->getValue() : "";
        $_a = $field->hasSubFields('a') ? $field->getNthSubField('a', 0)->getValue() : "";
        $_b = $field->hasSubFields('b') ? $field->getNthSubField('b', 0)->getValue() : "";
        $_c = $field->hasSubFields('c') ? $field->getNthSubField('c', 0)->getValue() : "";
        $_d = $field->hasSubFields('d') ? $field->getNthSubField('d', 0)->getValue() : "";
        $_x = $field->hasSubFields('x') ? $field->getNthSubField('x', 0)->getValue() : "";
        $_l = $field->hasSubFields('l') ? $field->getNthSubField('l', 0)->getValue() : "";


        if ($_d && !$_b && $_a && $_c) {
            return "$_a, $_d $_c";
        }

        if ($_a && $_b && $_c && $_x) {
            return "$_a &lt;$_c&gt; / $_b &lt$_x&gt;";
        }

        if ($_5 && $_l) {
            return "$_5 &lt;$_l&gt;";
        }

        if ($_8) {
            return $_8;
        }

        return "";
    }

    /**
     * @param Field $field
     * @return string
     */
    private function buildPublishDate($field)
    {
        $year = "";
        if (!empty($field)) {
            $p_011at_a = $p_011at_n = null;


            if ($field->hasSubFields('a')) {
                /** @var SubField $p_011at_a */
                $p_011at_a = $field->getSubFieldsWithCode('a')[0];
            }

            if ($field->hasSubFields('n')) {
                /** @var SubField $p_011at_n */
                $p_011at_n = $field->getSubFieldsWithCode('a')[0];
            }

            if (!empty($p_011at_a)) {
                $year = "(" . trim($p_011at_a->getValue()) . ")";
            }
            if (!empty($p_011at_n)) {

                if (preg_match('/\[.+\]/', $p_011at_n->getValue())) { //there are square brackets?
                    $year = $p_011at_n->getValue();
                    return $year; // do not use round brackets
                } else {
                    $year = "(" . $p_011at_n->getValue() . ")";
                    return $year; //otherwise
                }
            }

        }
        return $year;
    }
}
