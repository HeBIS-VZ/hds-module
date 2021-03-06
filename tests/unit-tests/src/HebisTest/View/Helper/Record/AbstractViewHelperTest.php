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

namespace HebisTest\View\Helper\Record;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Hebis\Exception\HebisException;
use Hebis\Marc\Helper;
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use HebisTest\View\Helper\SpreadsheetTestsTrait;
use HebisTest\View\Helper\TestRecordFromIndexTrait;
use HebisTest\View\Helper\TestRunnerTrait;
use Zend\Http\Client;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;


/**
 * Class AbstractViewHelperTest
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
abstract class AbstractViewHelperTest extends \VuFindTest\Unit\ViewHelperTestCase
{

    use TestRecordFromIndexTrait,
        SpreadsheetTestsTrait,
        TestRunnerTrait;

    const VIEW_HELPER_NAMESPACE = "Hebis\View\Helper\Record";

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

    /** @var string $testSheetName */
    protected $testSheetName;

    /**
     * @var \VuFind\Config\PluginFactory
     */
    protected $pluginFactory;

    /**
     * @var string ("SingleRecord"|"ResultList"|"BibTip")
     */
    protected $resultType;

    protected $spreadSheetName = "rda.xlsx";


    /**
     * @param $className
     * @return AbstractRecordViewHelper
     */
    private static function factory($className)
    {
        if (strpos($className, "ResultList") !== false) {
            $type = "ResultList\\";
        } elseif (strpos($className, "SingleRecord") !== false) {
            $type = "SingleRecord\\";
        } elseif (strpos($className, "BibTip") !== false) {
            $type = "BibTip\\";
        } else {
            $type = "";
        }

        $className = self::VIEW_HELPER_NAMESPACE . "\\" . $type . $className;

        if (!class_exists($className)) {
            //throw new HebisException("Class '$className' not found");
        }

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
                $message = "File '" . $testRecordFile . ".json' not found for Test " . $this->viewHelperClass;
                throw new \Exception($message);
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

    public function setUp()
    {
        $this->pluginFactory = new \VuFind\Config\PluginFactory();
        $this->viewHelper = self::factory($this->viewHelperClass);
        $this->viewHelper->setView($this->getPhpRenderer($this->getPlugins()));
        $this->spreadsheetReader = ReaderFactory::create(Type::XLSX);
        $this->spreadsheetReader->open(PHPUNIT_FIXTURES_HEBIS . "/spreadsheet/" . $this->spreadSheetName);

        $testClassName = get_class($this);

        if (strpos($testClassName, "SingleRecord") !== false || strpos($testClassName, "BibTip") !== false) {
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
            $relevantRows = $this->spreadsheetTestCases($this->spreadsheetReader, $this->testSheetName);
            if (empty($relevantRows)) {
                $this->markTestSkipped(
                    "No test case found!"
                );
            } else {
                $this->runTests($relevantRows);
            }

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

            $message = 'Testing "' . $this->viewHelperClass . '" using "' . $k . '.json"';
            $expected = htmlentities($this->expections[$k][$this->testResultField]);
            $actual = trim($this->stripTags($this->viewHelper->__invoke($this->fixtures[$k])));

            $this->assertEquals($expected, $actual, $message);
        }
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


    /**
     * Get mock translator.
     *
     * @param array $translations Key => value translation map.
     *
     * @return \VuFind\View\Helper\Root\Translate
     */
    protected function getMockTranslator($translations)
    {
        $callback = function ($str, $tokens, $default) use ($translations) {
            $m = $translations[$str];
            return !empty($m) ? $m : $str;
        };
        $translator = $this->getMock('VuFind\View\Helper\Root\Translate');
        $translator->method('translate')->will($this->returnCallback($callback));
        return $translator;
    }

}
