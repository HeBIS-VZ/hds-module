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

use Box\Spout\Common\Type;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Writer\Common\Sheet;
use Hebis\RecordDriver\SolrMarc;
use VuFindSearch\Backend\Exception\HttpErrorException;
use Zend\Http\Client;

/**
 * Class AbstractViewHelperTest
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
abstract class AbstractViewHelperTest extends \VuFindTest\Unit\ViewHelperTestCase
{

    const VIEW_HELPER_NAMESPACE = "\\Hebis\\View\\Helper\\Record";

    /**
     * @var array;
     */
    protected $testRecordIds = [];

    /**
     * @var string
     */
    protected $testResultField;


    protected $viewHelperClass;


    /** @var  AbstractRecordViewHelper */
    protected $viewHelper;

    protected $fixtures = [];

    protected $expections = [];

    /**
     * @var ReaderInterface
     */
    protected $spreadsheetReader;

    /** @var string $testSheetName  */
    protected $testSheetName;

    /**
     * @var \VuFind\Config\PluginFactory
     */
    protected $pluginFactory;

    /**
     * @var string ("SingleRecord"|"ResultList")
     */
    protected $resultType;

    /**
     * @param $className
     * @return AbstractRecordViewHelper
     */
    private static function factory($className)
    {
        $className = self::VIEW_HELPER_NAMESPACE . "\\" . $className;

        /** @var AbstractRecordViewHelper $helper */
        $helper = new $className();

        return $helper;
    }

    protected function initFixtures()
    {
        $this->readFixtures();
    }

    private function readFixtures()
    {

        foreach ($this->testRecordIds as $testRecordFile) {

            $fileName = PHPUNIT_FIXTURES_HEBIS . "/JsonSolrDocs/" . $testRecordFile . ".json";

            if (!is_file($fileName)) {
                throw new \Exception("File '" . $testRecordFile . ".json' not found for Test " . $this->viewHelperClass);
            }

            $file = file_get_contents($fileName);
            $jsonObject = json_decode($file, true);
            $marcObject = new SolrMarc();
            try {
                $marcObject->setRawData($jsonObject['record']);
            } catch (\File_MARC_Exception $e) {
                echo "$testRecordFile: " . $e->getMessage() . "\n";
                continue;
            }
            $this->fixtures[$jsonObject['record']['id']] = $marcObject;
            $this->expections[$jsonObject['record']['id']] = $jsonObject['expected_results'];
        }

    }

    /**
     * @param $ppn
     * @return SolrMarc|null
     * @throws \HttpException
     */
    protected function getRecordFromIndex($ppn)
    {
        $url = 'http://silbendrechsler.hebis.uni-frankfurt.de:8986/solr/hebis/select?wt=json&q=id:HEB' . $ppn;
        $client = new Client($url, array(
            'maxredirects' => 3,
            'timeout' => 10
        ));
        $response = $client->send();

        if ($response->getStatusCode() > 299) {
            throw new \HttpException("Status code " . $response->getStatusCode() . " for $url.");
        }
        $jsonString = trim($response->getBody());
        $jsonObject = json_decode($jsonString, true);
        $marcObject = new SolrMarc();

        if ($jsonObject['response']['numFound'] < 1) {
            $this->markTestIncomplete("No document found with ppn \"$ppn\". Skipping this test case...");
        }

        try {
            $marcObject->setRawData($jsonObject['response']['docs'][0]);
        } catch (\File_MARC_Exception $e) {
            echo "Record HEB$ppn: " . $e->getMessage() . "\n";
            return null;
        }
        return $marcObject;
    }

    public function setUp()
    {
        $this->pluginFactory = new \VuFind\Config\PluginFactory();
        $this->viewHelper = self::factory($this->viewHelperClass);
        $this->viewHelper->setView($this->getPhpRenderer($this->getPlugins()));
        $this->spreadsheetReader = ReaderFactory::create(Type::XLSX);
        $this->spreadsheetReader->open(PHPUNIT_FIXTURES_HEBIS . "/spreadsheet/rda.xlsx");

        $testClassName = get_class($this);

        if (strpos($testClassName, "SingleRecord") !== false) {
            $this->resultType = "SingleRecord";
        } else {
            $this->resultType = "ResultList";
        }

        if (empty($this->testSheetName)) {
            $this->initFixtures();
        }

    }

    public function test__invoke()
    {
        if (!empty($this->testSheetName)) {
            $this->spreadsheetTest();
        } else {
            $this->fixtureTest();
        }
    }


    protected function stripTags($string)
    {
        $string = preg_replace('/<br(\ ?\/?)>/', '', $string); //remove line breaks <br>
        $string = preg_replace('/<(p|a)[^>]*?>([^<\/]*)<\/\1>/', '$2', $string);
        return trim($string);

    }

    /**
     * @param IteratorInterface $rows
     */
    protected function runTests($rows)
    {
        
        for ($i = 0; $i < count($rows); ++$i) {
            $row = $rows[$i];
            list(
                $comment,
                $ppn,
                $testData,
                $expectedSingleRecordResult,
                $expectedRecordListResult) = array_slice($row, 0, 5);

            $record = $this->getRecordFromIndex($ppn);
            if (empty($record)) {
                if (empty($testData)) {

                    throw new \PHPUnit_Framework_IncompleteTestError('no data found');
                }
                //$record = $this->getRecordFromTestData($testData); TODO: implement
            }

            $actual = trim(strip_tags(str_replace("<br />", "\n", $this->viewHelper->__invoke($record))));
            $_comment = "Test: \"".$this->testSheetName."\", Class: \"".$this->viewHelperClass."\", Test Case: $i / PPN: ".$row[1]."; Comment: $comment\n";

            $this->executeTest($expectedSingleRecordResult, $actual, $_comment, $expectedRecordListResult);

        }
    }


    /**
     * @param $expectedSingleRecordResult
     * @param $actual
     * @param $_comment
     * @param $expectedRecordListResult
     */
    protected function executeTest($expectedSingleRecordResult, $actual, $_comment, $expectedRecordListResult)
    {
        if ($this->resultType == "SingleRecord") {
            $this->assertEquals(htmlentities(trim($expectedSingleRecordResult)), $actual, $_comment);
        } else {
            $this->assertEquals(htmlentities(trim($expectedRecordListResult)), $actual, $_comment);
        }
    }

    public function fixtureTest()
    {
        foreach ($this->testRecordIds as $k) {
            if (!array_key_exists($k, $this->expections)) {
                continue;
            }
            if (!array_key_exists($this->testResultField, $this->expections[$k])) {
                continue;
            }

            $message = 'Testing "'.$this->viewHelperClass.'" using "'.$k.'.json"';
            $expected = htmlentities($this->expections[$k][$this->testResultField]);
            $actual = trim($this->stripTags($this->viewHelper->__invoke($this->fixtures[$k])));

            $this->assertEquals($expected, $actual, $message);
        }
    }

    public function spreadsheetTest()
    {

        /** @var Sheet $sheet */
        foreach ($this->spreadsheetReader->getSheetIterator() as $sheet) {

            if ($sheet->getName() === $this->testSheetName) {
                $isRelevantRow = false;
                $relevantRows = [];
                /** @var array $row */
                foreach ($sheet->getRowIterator() as $row) {

                    if (strpos($row[0], "Genutzte Felder") !== false) {
                        $isRelevantRow = true;
                        continue;
                    }
                    if ($isRelevantRow) {
                        if (empty($row[0])) {
                            break;
                        }
                        $relevantRows[] = array_slice($row, 0, 6);
                    }
                }

                break;
            }
        }

        if (empty($relevantRows)) {
            $this->fail("No test case found!");
        }

        $this->runTests($relevantRows);
    }

    /**
     * Wrapper around factory
     *
     * @param string $name Configuration to load
     *
     * @return \Zend\Config\Config
     */
    public function getConfig($name)
    {
        return $this->pluginFactory->createServiceWithName(
            $this->getMock('Zend\ServiceManager\ServiceLocatorInterface'),
            $name, $name
        );
    }

    /**
     * Get plugins to register to support view helper being tested
     *
     * @return array
     */
    abstract protected function getPlugins();


}
