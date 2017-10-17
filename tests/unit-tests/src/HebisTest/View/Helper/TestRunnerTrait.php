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

namespace HebisTest\View\Helper;


use Box\Spout\Reader\IteratorInterface;

trait TestRunnerTrait
{


    /**
     * @param IteratorInterface $rows
     */
    protected function runTests($rows)
    {
        $failures = [];
        for ($i = 0; $i < count($rows); ++$i) {
            $row = $rows[$i];
            list(
                $comment,
                $ppn,
                $testData,
                $expectedSingleRecordResult,
                $expectedRecordListResult) = array_slice($row, 0, 5);

            if (empty($ppn)) {
                break;
            }
            $record = $this->getRecordFromIndex($ppn);

            if (is_null($record)) {
                $this->markTestIncomplete("No document found with ppn \"$ppn\". Skipping this test case...");
            }

            if (empty($record)) {
                if (empty($testData)) {

                    throw new \PHPUnit_Framework_IncompleteTestError('no data found');
                }
                //$record = $this->getRecordFromTestData($testData); TODO: implement
            }
            $res = str_replace("<br />", "\n", $this->viewHelper->__invoke($record));
            $actual = trim(strip_tags($res));
            $_comment = "Test: \"" . $this->testSheetName . "\", Class: \"" . $this->viewHelperClass . "\", Test Case: $i / PPN: " . $row[1] . "; Comment: $comment\n";

            try {
                $this->executeTest($expectedSingleRecordResult, $actual, $_comment, $expectedRecordListResult);
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $failures[] = $e;
            }

        }

        foreach ($failures as $e) {
            fwrite(STDERR, $e->toString() . "\n" . $e->getComparisonFailure()->getDiff() . "\n\n-----\nActual:\n" . $e->getComparisonFailure()->getActual() . "\n\n");
            fwrite(STDERR, "------------------------------------------------------------------------------------------------------------------------------------------------------\n\n");
        }

        if (count($failures) > 0) {
            $this->fail("This Test has failed!");
        }
    }
}