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

namespace Hebis\RecordDriver;


class PicaRecordUtils
{

    const PICA_TIT_SEARCH_PATTERN = '/--.+--:/';

    const PICA_TIT_SEARCH_PATTERN2 = '/--.+--/';


    /**
     * Process level1 data
     *
     * @return void
     * @access private
     */
    private static function _processPicaLevel1(PicaRecordInterface $picaRecord)
    {

        $picaLevel1 = $picaRecord->getLevel1();

        foreach ($picaLevel1 as $key => $arr) {

            // With ILN do exact match, without ILN find matching EPNs
            $lok = $picaRecord->getIln() ? 'lok: ' . $picaRecord->getEpn() . ' ' . $picaRecord->getIln() : $picaRecord->getEpn();

            $found = $picaRecord->getIln() ? (($key === $lok) ? true : false) : (strpos($key, $lok) > -1 ? true : false);

            if ($found) {
                $klassArray = array();
                $schlagArray = array();
                $sumArray = array();

                foreach ($picaRecord->getLevel1()[$key] as $fieldsKey => $fields) {
                    if ($fieldsKey === '147I') {

                        $sumArray = self::f147i($fields, $sumArray);
                    }
                    if ($fieldsKey === '145Z' || $fieldsKey === '144Z') {
                        list($klassArray, $schlagArray) = self::f14xz($fields, $fieldsKey, $klassArray, $schlagArray);
                    }
                }
                $levelOneData = [
                    'lvl1klassifikationen' => $klassArray,
                    'lvl1schlagworte' => $schlagArray,
                    'lvl1zus' => $sumArray
                ];

                break;
            }
        }
    }

    public static function _processPicaLevel0(array $picaTit)
    {

        $f036A = array();
        $f036B = array();
        $f036C = array();
        $f036D = array();
        $f036F = array();
        $f036G = array();

        $jorunal = self::getJournal($picaTit);
        $volumes = self::getVolumes($picaTit);
        $retroUrl = self::getRetroUrl($picaTit);


        foreach ($picaTit as $field => $fieldArray) {

            if ($field === '009P' || strpos($field, '036') === 0 || strpos($field, '039') === 0) {

                foreach ($fieldArray as $subField) {
                    // getSeries
                    $count = $subField['count'];

                    if ($field === '036B') {
                        $f036B = self::f036b($subField, $f036B);
                        break;
                    }

                    if ($field === '036A') {
                        $f036A = self::f036a($subField, $f036A);
                        break;
                    }

                    if ($field === '036D') {
                        $f036D = self::f036d($subField, $f036D);
                        break;
                    }

                    if ($field === '036C') {
                        list($f036A, $f036C) = self::f036c($subField, $f036A, $f036C);
                        break;
                    }

                    if ($field === '036F') {
                        $f036F = self::f036f($subField, $f036F, $count);
                        break;
                    }

                    if ($field === '036G') {

                        $f036G = self::f036g($subField, $f036G);
                        break;
                    }

                    if (isset($subField['$9'])) {
                        // getReviewed
                        if ($field === '039T') {
                            foreach ($subField['$9'] as $index => $value) {
                                $reviewed[$value] = $subField['$8'][$index];
                            }
                        }

                        // getReview
                        if ($field === '039U') {
                            foreach ($subField['$9'] as $index => $value) {
                                $review[$value] = $subField['$8'][$index];
                            }
                        }
                    }

                    if (preg_match('/^039[BCSEDX]/', $field)) {

                        if (isset($subField['$a'])) {

                            foreach ($subField['$a'] as $index => &$value) {

                                $subFieldArray98ac = [
                                    '9' => isset($subField['$9']) ? $subField['$9'][$index] : false,
                                    '8' => isset($subField['$8']) ? preg_replace(self::PICA_TIT_SEARCH_PATTERN2, '', preg_replace(self::PICA_TIT_SEARCH_PATTERN, '', $subField['$8'][$index])) : false,
                                    'a' => $subField['$a'][$index],
                                    'c' => isset($subField['$c']) ? $subField['$c'][$index] : false
                                ];

                                // getJBibContext
                                if ((strpos($picaTit['002@']['0']['$0']['0'], "b") === 1 || strpos($picaTit['002@']['0']['$0']['0'], "d") === 1) && $field === '039B') {
                                    $jBibContext[] = $subFieldArray98ac;
                                    continue;
                                }

                                if ($field === '039C' || $field === '039S') {

                                    $jBibContext[] = $subFieldArray98ac;
                                    continue;
                                }

                                // getJournalPrePost
                                if ($field === '039E') {
                                    $journalPrePost[] = $subFieldArray98ac;
                                    continue;
                                }

                                // getOtherEditions
                                if ($field === '039D' || $field === '039X') {
                                    $otherEditions[] = $subFieldArray98ac;
                                    continue;
                                }
                            }
                        }
                    }

                    // getAllTitleLinks
                    if ($field === '009P') {
                        $allTitleLinks = self::allTitleLinks($subField, []);

                    }
                }
            }
        }
        // getSeries
        $series = [$f036A, $f036B, $f036C, $f036D, $f036F, $f036G];

    }

    private static function getJournal(array $picaTit)
    {

        $replacement = '';
        $journal = [];
        // getJournal
        if (strpos($picaTit['002@']['0']['$0']['0'], "o") === 1) {

            if (!empty($picaTit['039B']['0']['$9']['0'])) {
                $journal['ppn'] = $picaTit['039B']['0']['$9']['0'];
            }

            if (!empty($picaTit['039B']['0']['$a']['0'])) {
                $journal['prefix'] = $picaTit['039B']['0']['$a']['0'];
            }

            if (!empty($picaTit['039B']['0']['$8']['0'])) {
                $replacement1 = preg_replace(self::PICA_TIT_SEARCH_PATTERN, $replacement, $picaTit['039B']['0']['$8']['0']);
                $journal['name'] = preg_replace(self::PICA_TIT_SEARCH_PATTERN2, $replacement, $replacement1);
            }

            if (!empty($picaTit['039B']['0']['$c']['0']) && !isset($journal['name'])) {
                $journal['name'] = $picaTit['039B']['0']['$c']['0'];
            }

            if (!empty($picaTit['031A']['0']['$d']['0'])) {
                $journal['band'] = $picaTit['031A']['0']['$d']['0'];
            }

            if (!empty($picaTit['031A']['0']['$j']['0'])) {
                $journal['jahr'] = $picaTit['031A']['0']['$j']['0'];
            }

            if (!empty($picaTit['031A']['0']['$e']['0'])) {
                $journal['kommentar'] = $picaTit['031A']['0']['$e']['0'];
            }

            if (!empty($picaTit['031A']['0']['$h']['0'])) {
                $journal['seite'] = $picaTit['031A']['0']['$h']['0'];
            }
        }
        return $journal;
    }

    private static function getVolumes($picaTit)
    {
        if (strpos($picaTit['002@']['0']['$0']['0'], "c") === 1 || strpos($picaTit['002@']['0']['$0']['0'], "d") === 1) {
            $volumes[$picaTit['003@']['0']['$0']['0']] = 'allvolumes';
        }
        return $volumes;
    }

    private static function getRetroUrl($picaTit)
    {
        // getRetroUrl
        // preliminary solution for detection of series
        if (isset($picaTit['009R']) && strlen($picaTit['009R']['0']['$u']['0']) > 0 &&
            isset($picaTit['002@']) && strpos($picaTit['002@']['0']['$0']['0'], "r") === 0
        )
            $retroUrl = array($picaTit['009R']['0']['$u']['0'], $picaTit['009R']['0']['$3']['0']);

        return $retroUrl;
    }

    /**
     * @param $subfield
     * @param $f036D
     *
     * @return array
     */
    private static function f036d($subfield, $f036D)
    {
        $count = $subfield['count'];

        if (isset($subfield['$9'])) {
            $f036D[$count]['ppn'] = $subfield['$9']['0'];
        }

        if (isset($subfield['$8'])) {
            $f036D[$count]['text1'] = str_replace("@", "", $subfield['$8']['0']);
        }

        return $f036D;
    }

    /**
     * @param $subField
     * @param $f036A
     * @param $f036C
     * @return array
     */
    private static function f036c($subField, $f036A, $f036C)
    {
        $count = $subField['count'];
        if ($count === '00') {
            if (isset($subField['$l'])) {
                $f036A[$count]['text3'] = $subField['$l']['0'];
            }

        } else {

            if (isset($subField['$m'])) {
                $f036C[$count]['text1'] = $subField['$m']['0'];
            }

            if (isset($subField['$a'])) {
                $f036C[$count]['text2'] = str_replace("@", "", $subField['$a']['0']);
            }

            if (isset($subField['$l'])) {
                $f036C[$count]['text3'] = $subField['$l']['0'];
            }

        }

        return array($f036A, $f036C);
    }

    /**
     * @param $subField
     * @param $f036A
     * @return array
     */
    private static function f036a($subField, $f036A)
    {
        $count = $subField['count'];

        if ($count === '00') {

            if (isset($subField['$l'])) {
                $f036A[$count]['text3'] = $subField['$l']['0'];

            }
        } else {

            if (isset($subField['$m'])) {
                $f036A[$count]['text1'] = $subField['$m']['0'];
            }

            if (isset($subField['$a'])) {
                $f036A[$count]['text2'] = str_replace("@", "", $subField['$a']['0']);
            }

            if (isset($subField['$l'])) {
                $f036A[$count]['text3'] = $subField['$l']['0'];

            }
        }

        return $f036A;
    }

    /**
     * @param $subField
     * @param $f036B
     * @return array
     */
    private static function f036b($subField, $f036B)
    {
        $count = $subField['count'];

        if (isset($subField['$9'])) {
            $f036B[$count]['ppn'] = $subField['$9']['0'];
        }

        if (isset($subField['$8'])) {
            $f036B[$count]['text1'] = str_replace("@", "", $subField['$8']['0']);
        }

        return $f036B;
    }

    /**
     * @param $subField
     * @param $f036F
     * @return array
     */
    private static function f036f($subField, $f036F)
    {
        $count = $subField['count'];

        if (isset($subField['$9'])) {
            $f036F[$count]['ppn'] = $subField['$9']['0'];
        }

        if (isset($subField['$8'])) {
            $f036F[$count]['text1'] = str_replace("@", "", $subField['$8']['0']);
        }

        if (isset($subField['$l'])) {
            $f036F[$count]['text2'] = $subField['$l']['0'];
        }

        return $f036F;
    }

    /**
     * @param $subField
     * @param $f036G
     * @return array
     */
    private static function f036g($subField, $f036G)
    {
        $count = $subField['count'];
        $f036G[$count]['text2'] = '';

        if (isset($subField['$a']))
            $f036G[$count]['text1'] = $subField['$a']['0'];

        if (isset($subField['$d'])) {
            foreach ($subField['$d'] as $index => $value) {
                $f036G[$count]['text2'] = $f036G[$count]['text2'] . ' : ' . $value;
            }

        }
        return $f036G;
    }

    /**
     * @param $subField
     * @param $allTitleLinks
     * @return array
     */
    private static function allTitleLinks($subField, $allTitleLinks)
    {
        if (isset($subField['$3']) || isset($subField['$u'])) {
            $count = isset($subField['$3']) ? count($subField['$3']) : count($subField['$u']);

            for ($i = 0; $i < $count; ++$i) {
                $title = isset($subField['$3']) ? $subField['$3'][$i] : 'Hinweise zum Inhalt';
                $url = isset($subField['$u']) ? $subField['$u'][$i] : 'xxx';
                $display = (strlen($url) > 30) ? '...' . substr($url, -27) : $url;

                $allTitleLinks[] = [
                    'title' => $title,
                    'value' => $display,
                    'link' => $url
                ];

            }
        }
        return $allTitleLinks;
    }

    /**
     * @param $fields
     * @param $sumArray
     * @return array
     */
    private static function f147i($fields, $sumArray)
    {
        foreach ($fields as $subField) {
            if (isset($subField['$a']) && $subField['count'] === '50') {
                foreach ($subField['$a'] as $value) {
                    $sumArray[] = $value;
                }
            }
        }
        return $sumArray;
    }

    /**
     * @param $fields
     * @param $fieldsKey
     * @param $klassArray
     * @param $schlagArray
     * @return array
     */
    private static function f14xz($fields, $fieldsKey, $klassArray, $schlagArray)
    {
        foreach ($fields as $subField) {
            if (isset($subField['$a'])) {
                foreach ($subField['$a'] as $value) {
                    // getlvlOneData
                    // Klassifikation
                    if ($fieldsKey === '145Z')
                        $klassArray[] = $value;

                    // Schlagworte
                    if ($fieldsKey === '144Z')
                        $schlagArray[] = "#;#;#" . $value;
                }
            }
        }
        return array($klassArray, $schlagArray);
    }

    /**
     * @param $subField
     * @return array
     */
    private static function f244z($subField)
    {
        $subFelder = array();
        if ($subField['$x']['0'] >= 80 && $subField['$x']['0'] <= 98) {
            $subFelder['vorbesitzer'] = $subField['$8']['0'];
            $subFelder['ppn'] = $subField['$9']['0'];

            //Pseudosubfelder
            foreach ($subField as $key => $value) {
                if ($key === '$8') {
                    continue;
                }
                if ($key === '$P' || $key === '$c' || $key === '$l' || $key === '$8') {

                    $subFelder['pseudosubfelder'][str_replace('$', '', $key)] = $value[0];

                } else if ($key === '$n' || $key === '$g' || $key === '$b') { //wiederholbare Felder

                    $subFelder['pseudosubfelder'][str_replace('$', '', $key)][] = $value[0];
                }
            }
        }

        if ($subField['$x']['0'] == 99) {
            $subFelder['deskriptoren'] = $subField['$a']['0'];
        }

        return $subFelder;
    }
}