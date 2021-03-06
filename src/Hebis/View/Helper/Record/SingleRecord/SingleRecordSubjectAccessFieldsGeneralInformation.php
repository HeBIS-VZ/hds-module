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
use Hebis\Marc\Helper;

/**
 * Class SingleRecordSubjectAccessFieldsGeneralInformation
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordSubjectAccessFieldsGeneralInformation extends AbstractRecordViewHelper
{
    /**
     * @param SolrMarc $record
     * @return string|array
     */
    public function __invoke(SolrMarc $record, $asArray = false)
    {
        $arr = [];
        $line600 = ($this->add600($record));
        if (!empty($line600)) {
            $arr[] = $line600;
        }
        $line610 = ($this->add610($record));
        if (!empty($line610)) {
            $arr[] = $line610;
        }
        $line611 = ($this->add611($record));
        if (!empty($line611)) {
            $arr[] = $line611;
        }
        $line630 = ($this->add630($record));
        if (!empty($line630)) {
            $arr[] = $line630;
        }
        $line650 = ($this->add650($record));
        if (!empty($line650)) {
            $arr[] = $line650;
        }
        $line651 = ($this->add651($record));
        if (!empty($line651)) {
            $arr[] = $line651;
        }
        $line655 = ($this->add655($record));
        if (!empty($line655)) {
            $arr[] = $line655;
        }
        $line648 = ($this->add648($record));
        if (!empty($line648)) {
            $arr[] = $line648;
        }

        if (!$asArray) {
            return implode("<br />", $arr);
        } else {
            return $arr;
        }

    }

    /**
     * @param SolrMarc $record
     * @return string
     * @internal param $line
     */
    private function add600(SolrMarc $record)
    {
        $arr = [];
        /** @var \File_MARC_Data_Field $field */
        foreach ($record->getMarcRecord()->getFields(600) as $field) {
            $arr_ = [];
            $completeTag = "";
            /** @var \File_MARC_Subfield $sf */
            foreach ($field->getSubfields() as $sf) {
                if (!in_array($sf->getCode(), ['a', 'b', 'c', 't', 'x'])) {
                    continue;
                }
                $line = "";         // 600 $a__$b_<$c>_/_$t,_$x
                switch ($sf->getCode()) {
                    case 'a':
                        $line .= !empty($sf) ? htmlentities($sf->getData()) : "";
                        break;
                    case 'b':
                        $line .= !empty($sf) ? " " . htmlentities($sf->getData()) : "";
                        break;
                    case 'c':
                        $line .= !empty($sf) ? " &lt;" . htmlentities($sf->getData()) . "&gt;" : "";
                        break;
                    case 't':
                        $line .= !empty($sf) ? " / " . htmlentities($sf->getData()) : "";
                        break;
                    case 'x':
                        $line .= !empty($sf) ? ", " . htmlentities($sf->getData()) : "";
                        break;
                }
                $arr_[] = $line;
            }
            $generatedKeywords = $this->generateTag($field, $arr_);
            if (!empty($generatedKeywords)) {
                $arr[] = "<nobr>" . $generatedKeywords . "</nobr>";
            }
        }
        return implode("<br />", $arr);
    }


    private function add610($record)
    {
        $arr = [];
        /** @var \File_MARC_Data_Field $field */
        foreach ($record->getMarcRecord()->getFields(610) as $field) {
            /** @var \File_MARC_Subfield $sf */
            $arr_ = "";
            foreach ($field->getSubfields() as $sf) {
                if (!empty($sf)) {
                    if (!in_array($sf->getCode(), ['a', 'b', 'g', 't', 'f', 'x'])) {
                        continue;
                    }

                    // 610 $a_/_$b_<$g>_/_$t_($f),_$x

                    switch ($sf->getCode()) {
                        case 'a':
                            $arr_[] = htmlentities($sf->getData());
                            break;
                        case 'b':
                            $arr_[] = " / " . htmlentities($sf->getData());
                            break;
                        case 'g':
                            $arr_[] = " &lt;" . htmlentities($sf->getData()) . "&gt;";
                            break;
                        case 't':
                            $arr_[] = " / " . htmlentities($sf->getData());
                            break;
                        case 'f':
                            $arr_[] = " (" . htmlentities($sf->getData()) . ")";
                            break;
                        case 'x':
                            $arr_[] = ", " . htmlentities($sf->getData());
                            break;
                    }
                }
            }
            $generatedKeywords = $this->generateTag($field, $arr_);
            if (!empty($generatedKeywords)) {
                $arr[] = "<nobr>" . $generatedKeywords . "</nobr>";
            }
        }
        return implode("<br />", $arr);
    }

    private function add611(SolrMarc $record)
    {
        $arr = [];
        /** @var \File_MARC_Data_Field $field */
        foreach ($record->getMarcRecord()->getFields(611) as $field) {

            $arr_ = [];
            $subFields = $field->getSubfields();

            foreach ($subFields as $sf) {

                $code = $sf->getCode();

                if (!in_array($code, ['a', 'c', 'd', 'e', 'f', 'g', 'n', 't', 'x'])) {
                    continue;
                }

                switch ($code) {

                    case 'a':
                        $arr_[] = htmlentities($sf->getData());
                        break;
                    case 'c':
                        $arr_[] = ", " . htmlentities($sf->getData());
                        break;
                    case 'd':
                        $arr_[] = ", " . htmlentities($sf->getData());
                        break;
                    case 'e':
                        $arr_[] = " / " . htmlentities($sf->getData());
                        break;
                    case 'f':
                        $arr_[] = ", " . htmlentities($sf->getData());
                        break;
                    case 'g':
                        $arr_[] = " &lt;" . htmlentities($sf->getData()) . "&gt;";
                        break;
                    case 'n':
                        $arr_[] = ", " . htmlentities($sf->getData());
                        break;
                    case 't':
                        $arr_[] = " / " . htmlentities($sf->getData());
                        break;
                    case 'x':
                        $arr_[] = ", " . htmlentities($sf->getData());
                        break;
                }


            }
            $generatedKeywords = $this->generateTag($field, $arr_);

            if (!empty($generatedKeywords)) {
                $arr[] = "<nobr>" . $generatedKeywords . "</nobr>";

            }
        }
        return implode("<br />", $arr);
    }

    private function add630($record)
    {
        $arr = [];
        foreach ($record->getMarcRecord()->getFields(630) as $field) {
            $subFields = $field->getSubfields();
            $arr_ = [];

            foreach ($subFields as $sf) {
                $data = htmlentities($sf->getData());
                $code = $sf->getCode();
                // 630 $a,_$d,_$e,_$f_<$g>,_$n._$s_/_$t,_$x

                switch ($code) {
                    case 'a':
                        $arr_[] = $data;
                        break;
                    case 'd':
                        $arr_[] = ", " . $data;
                        break;
                    case 'e':
                        $arr_[] = ", " . $data;
                        break;
                    case 'f':
                        $arr_[] = ", " . $data;
                        break;
                    case 'g':
                        $arr_[] = " &lt;" . $data . "&gt;";
                        break;
                    case 'n':
                        $arr_[] = ", " . $data;
                        break;
                    case 's':
                        $arr_[] = ". " . $data;
                        break;
                    case 't':
                        $arr_[] = " / " . $data;
                        break;
                    case 'x':
                        $arr_[] = ", " . $data;
                        break;

                }

            }

            $generatedKeywords = $this->generateTag($field, $arr_);

            if (!empty($generatedKeywords)) {
                $arr[] = "<nobr>" . $generatedKeywords . "</nobr>";
            }
        }
        return implode("<br />", $arr);
    }

    private function add650($record)
    {
        $arr = [];
        foreach ($record->getMarcRecord()->getFields(650) as $field) {
            $sf_ = $this->getSubFieldsDataOfField($field, ['a', 'c', 'x', 'g']);

            $arr_ = [];
            foreach ($sf_ as $sf) {         // 650 $a_<$c>,_$x_<$g>
                if (!empty($sf['a'])) {
                    $arr_[] = $sf['a'];
                }
                if (!empty($sf['c'])) {
                    $arr_[] = " &lt;" . $sf['c'] . "&gt;";
                }
                if (!empty($sf['x'])) {
                    $arr_[] = ", " . $sf['x'];
                }
                if (!empty($sf['g'])) {
                    $arr_[] = " &lt;" . str_replace("g:", "", $sf['g']) . "&gt;";
                }

            }
            $generatedKeywords = $this->generateTag($field, $arr_);
            if (!empty($generatedKeywords)) {
                $arr[] = "<nobr>" . $generatedKeywords . "</nobr>";
            }
        }
        return implode("<br />", $arr);


    }

    private function add651($record)
    {
        $arr = [];

        foreach ($record->getMarcRecord()->getFields(651) as $field) {
            $sf_ = $this->getSubFieldsDataOfField($field, ['a', 'g', 'x', 'z']);
            $arr_ = [];
            foreach ($sf_ as $sf) {
                if (!empty($sf['a'])) {
                    $arr_[] = $sf['a'];
                }
                if (!empty($sf['g'])) {
                    $arr_[] = ", " . $sf['g'];
                }
                if (!empty($sf['x'])) {
                    $arr_[] = " / " . $sf['x'];
                }
                if (!empty($sf['z'])) {
                    $arr_[] = ", " . $sf['z'];
                }
                $arr[] = "<nobr>" . $this->generateTag($field, $arr_) . "</nobr>";
            }
        }
        return implode("<br />", $arr);
    }

    private function add655($record)
    {
        $arr = [];
        foreach ($record->getMarcRecord()->getFields(655) as $field) {
            $sf_ = $this->getSubFieldsDataOfField($field, ['a', 'x', 'y', 'z']);
            foreach ($sf_ as $sf) {
                $arr[] = "<nobr>" . $this->generateTag($field, [implode(", ", $sf)]) . "</nobr>";
            }
        }
        return implode("<br />", $arr);
    }

    private function add648($record)
    {
        $field = $record->getMarcRecord()->getField('648');
        $_648_a = Helper::getSubFieldDataOfGivenField($field, 'a');
        $tag = $this->generateTag($field, [$_648_a]);
        return !empty($tag) ? "<nobr>" . $tag . "</nobr>" : "";
    }

    /**
     * @param \File_MARC_Data_Field $field
     * @param $arr
     * @return string
     */
    private function generateTag($field, $arr)
    {
        if (!empty($field)) {
            $_0 = $field->getSubfields('0');
            if (!empty($_0)) {
                $gnd = str_replace("(DE-603)", "", array_pop($_0)->getData());
                $completeTag = '<a href="' . $this->getUrl($gnd) . '">' . Helper::removeControlSigns(implode("", $arr)) . '</a>';
                return $this->makeCheckboxField($gnd, $completeTag);
            } else {
                // search for string in topic
                $topic = Helper::removeControlSigns(implode("", $arr));
                $completeTag = '<a href="' . $this->getSearchTopicUrl($topic) . '">' . $topic . '</a>';
                return $this->makeCheckboxTopicField($topic, $completeTag);
            }
        }
        return implode(" ", $arr);
    }

    private function getUrl($gnd)
    {
        return $this->getView()->basePath() . "/" .
            sprintf(
                "Search/Results?lookfor=%s&type=allfields",
                "uses_authority:%22$gnd%22"
            );
    }

    private function makeCheckboxField($gnd, $content)
    {
        $gnd = trim($gnd);
        $value = "uses_authority:&quot;$gnd&quot;";
        return '<label class="checkbox-inline"><input type="checkbox" name="lookfor0[]" value="' . $value . '" />'
            . '<input type="hidden" name="type0[]" value="allfields" />' . $content . '</label>';
    }

    private function makeCheckboxTopicField($topic, $content)
    {
        return '<label class="checkbox-inline"><input type="checkbox" name="lookfor0[]" value="' . trim($topic) . '" />'
            . '<input type="hidden" name="type0[]" value="topic" />' . $content . '</label>';
    }

    private function getSearchTopicUrl($topic)
    {
        return $this->getView()->basePath() . "/" .
            sprintf(
                "Search/Results?lookfor=%s&type=topic",
                "%22$topic%22"
            );
    }
}