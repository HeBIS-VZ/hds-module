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

namespace HebisTest\View\Helper\Hebisbs3;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Hebis\View\Helper\Hebisbs3\Factory;
use Hebis\View\Helper\Hebisbs3\MultipartItems;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use HebisTest\View\Helper\SpreadsheetTestsTrait;
use HebisTest\View\Helper\TestRecordFromIndexTrait;

class MultipartItemsTest extends \VuFindTest\Unit\ViewHelperTestCase
{

    use TestRecordFromIndexTrait,
        SpreadsheetTestsTrait;
    /**
     * @var MultipartItems
     */
    private $multipartItemsViewHelper;

    /** @var  AbstractRecordViewHelper */
    protected $viewHelper;

    /**
     * @var \VuFind\Config\PluginFactory
     */
    protected $pluginFactory;


    public function setUp()
    {
        parent::setUp();
        $this->pluginFactory = new \VuFind\Config\PluginFactory();
        $this->multipartItemsViewHelper = Factory::getMultipartItems(null);
        $this->multipartItemsViewHelper->setView($this->getPhpRenderer($this->getPlugins()));
    }

    public function testRenderParentalItem()
    {
        $relevantRows = $this->getRelevantRows();
        $this->runTestsForParentalItems($relevantRows, "renderParentalItem");

    }

    public function testRenderShowAllVolumesLink()
    {
        /*
        //case 1: from multipart item
        $ppn = "247447544";
        $expected = "<a href=\"/hds/Search/Results?sort=relevance&type0%5B%5D=part_of&lookfor0%5B%5D=247447544&join=AND\">show all volumes</a>";
        $this->runTestsForShowAllVolumes($ppn, $expected);

        //case 2: from child of a multipart item
        $ppn = "316097276";
        $this->runTestsForShowAllVolumes($ppn, $expected); //same link expected
        */
    }

    public function getRelevantRows()
    {
        $spreadsheetReader = ReaderFactory::create(Type::XLSX);
        $spreadsheetReader->open(PHPUNIT_FIXTURES_HEBIS . "/spreadsheet/rda.xlsx");
        return $this->spreadsheetTestCases($spreadsheetReader, "serie_mehrbaendig");
    }

    private function runTestsForParentalItems($rows, $function)
    {
        for ($i = 0; $i < count($rows); ++$i) {
            $row = $rows[$i];
            list(
                $comment,
                $ppn,
                $testData,
                $expected,
                $expectedRL) = array_slice($row, 0, 5);

            if (empty($ppn)) {
                continue;
            }
            $record = $this->getRecordFromIndex($ppn);
            $this->multipartItemsViewHelper->__invoke($record);
            $actual = trim(strip_tags(str_replace("<br />", "\n", call_user_func_array([$this->multipartItemsViewHelper, $function], [$testData]))));
            $_comment = "Test: \"serie_mehrbaendig\", Class: \"MultipartItemsTest\", Test Case: $i / PPN: " . $row[1] . "; Comment: $comment\n";
            try {
                $this->executeTest($expected, $actual, $_comment);
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $failures[] = $e;
            }
        }
    }

    public function executeTest($expected, $actual, $comment)
    {
        $this->assertEquals($expected, $actual, $comment);
    }


    /**
     * Get plugins to register to support view helper being tested
     *
     * @return array
     */
    protected function getPlugins()
    {
        $basePath = $this->getMock('Zend\View\Helper\BasePath');
        $basePath->expects($this->any())->method('__invoke')
            ->will($this->returnValue('/vufind2'));

        $url = $this->getMock('Zend\View\Helper\Url');
        $url->expects($this->any())->method('__invoke')
            ->will($this->returnValue('/hds/Search/Results'));

        $transEsc = $this->getMock("VuFind\View\Helper\Root\TransEsc");
        $transEsc->expects($this->any())->method('__invoke')
            ->will($this->returnValue('show all volumes'));
        return [
            'basepath' => $basePath,
            'url' => $url,
            'transesc' => $transEsc
        ];
    }

    /**
     * TODO: repair this test!
     * @param $ppn
     * @param $expected
     *
    private function runTestsForShowAllVolumes($ppn, $expected)
    {
        $record = $this->getRecordFromIndex($ppn);
        $this->multipartItemsViewHelper->__invoke($record);
        $actual = call_user_func([$this->multipartItemsViewHelper, "renderShowAllVolumesLink"]);
        $this->executeTest($expected, $actual, "link show all volumes of multipart item");
    }
    */
}
