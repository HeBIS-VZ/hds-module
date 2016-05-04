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

namespace Hebis\View\Helper;


class SingleRecordSubjectAddedKeywordsTest extends AbstractViewHelperTest
{
    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordSubjectAddedKeywords";
        $this->testResultField = "subject_added_keywords";
        $this->testRecordIds = [
            'HEB104075848',
            'HEB185769748',
            'HEB104076410',
            'HEB303357886',
            'HEB212739271',
            'HEB274180618',
            'HEB289661153',
            'HEB061371955',
            'HEB107443414',
            'HEB04567986X',
            'HEB110911911',
            'HEB072273666',
            'HEB306409275',
            'HEB109899237'
        ];

        parent::setUp();
    }

    /*
    public function test__invoke()
    {

        foreach ($this->testRecordIds as $k) {

            if (!array_key_exists($k, $this->expections)) {
                continue;
            }
            if (!array_key_exists($this->testResultField, $this->expections[$k])) {
                continue;
            }

            $this->assertEquals(
                $this->expections[$k][$this->testResultField],
                implode(" ; ", $this->viewHelper->__invoke($this->fixtures[$k])), 'Testing "' . $this->viewHelperClass . '" using "' . $k . '.json"');
        }

    }
    */
    /**
     * Get plugins to register to support view helper being tested
     *
     * @return array
     */
    protected function getPlugins()
    {
        return [];
    }
}