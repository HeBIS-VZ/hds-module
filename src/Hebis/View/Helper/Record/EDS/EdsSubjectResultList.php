<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an 
 * extension of the open source library search engine VuFind, that 
 * allows users to search and browse beyond resources. More 
 * Information about VuFind you will find on http://www.vufind.org
 * 
 * Copyright (C) 2017 
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

namespace Hebis\View\Helper\Record\EDS;


use VuFind\RecordDriver\EDS;
use Zend\View\Helper\AbstractHelper;

/**
 * Class EdsSubjectResultList
 * @package Hebis\View\Helper\Record\EDS
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class EdsSubjectResultList extends AbstractHelper
{

    public function __invoke(EDS $record)
    {
        $ret = [];
        $bibRecord = $record->getFields()['RecordInfo']['BibRecord'];
        if (array_key_exists("BibEntity", $bibRecord) &&
            array_key_exists("Subjects", $bibRecord['BibEntity'])
        ) {
            $subjects = array_filter($bibRecord['BibEntity']['Subjects'], function ($subject) {
                return isset($subject['SubjectFull']) && $subject['Type'] === "general";
            });

            $i = 0;
            foreach ($subjects as $subject) {
                $ret[] = '<span class="label label-subject" title="' . $this->getView()->escapeHtmlAttr($subject['SubjectFull']) . '">' . $this->getView()->truncate($subject['SubjectFull'], 24) . '</span>';
                ++$i;

                if ($i >= 6 && count($subjects) > 6) {
                    $ret[] = '<span class="label label-subject" title="' . $this->subjectsFrom($subjects, 6) . '">&hellip;</span>';
                    break;
                }
            }
        }
        return implode(" ", $ret);
    }

    /**
     * @param $subjects
     * @param $from
     */
    private function subjectsFrom($subjects, $from)
    {
        $res = [];
        $i = 0;
        foreach ($subjects as $subject) {
            if ($i < 6) {
                ++$i;
                continue;
            }
            $res[] = $subject['SubjectFull'];
            ++$i;
        }

        return $this->getView()->escapeHtmlAttr(implode("; ", $res));
    }
}