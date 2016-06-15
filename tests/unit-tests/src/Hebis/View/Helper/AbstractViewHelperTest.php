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

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Hebis\RecordDriver\SolrMarc;
use VuFindSearch\Backend\Exception\HttpErrorException;
use Zend\Http\Client;

abstract class AbstractViewHelperTest extends \VuFindTest\Unit\ViewHelperTestCase
{

    const VIEW_HELPER_NAMESPACE = "\\Hebis\\View\\Helper";

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

    /**
     * @param $className
     * @return AbstractRecordViewHelper
     */
    private static function factory($className)
    {
        $className = self::VIEW_HELPER_NAMESPACE."\\".$className;

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

            $fileName = PHPUNIT_FIXTURES_HEBIS."/JsonSolrDocs/".$testRecordFile.".json";

            if (!is_file($fileName)) {
                throw new \Exception("File '".$testRecordFile.".json' not found for Test ". $this->viewHelperClass);
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
        $url = 'http://solr.hebis.de/verbund/select?wt=json&q=id:HEB'.$ppn;
        $client = new Client($url, array(
            'maxredirects' => 3,
            'timeout'      => 10
        ));
        $response = $client->send();

        if ($response->getStatusCode() > 299) {
            throw new \HttpException("Status code ". $response->getStatusCode()." for $url.");
        }
        $jsonString = trim($response->getBody());
        $jsonObject = json_decode($jsonString, true);
        $marcObject = new SolrMarc();
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
        $this->viewHelper = self::factory($this->viewHelperClass);
        $this->viewHelper->setView($this->getPhpRenderer($this->getPlugins()));
        $this->spreadsheetReader = ReaderFactory::create(Type::ODS);
        $this->spreadsheetReader->open(PHPUNIT_FIXTURES_HEBIS."/spreadsheet/rda.ods");
        
        $this->initFixtures();
    }

    public function test__invoke()
    {
        foreach ($this->testRecordIds as $k) {
            if (!array_key_exists($k, $this->expections)) {
                continue;
            }
            if (!array_key_exists($this->testResultField, $this->expections[$k])) {
                continue;
            }

            $message = 'Testing "'.$this->viewHelperClass.'" using "'.$k.'.json"';
            $expected = $this->expections[$k][$this->testResultField];
            $actual = trim($this->stripTags($this->viewHelper->__invoke($this->fixtures[$k])));

            $this->assertEquals($expected, $actual, $message);
        }

    }


    protected function stripTags($string)
    {

        $string = preg_replace('/<br(\ ?\/?)>/', '', $string); //remove line breaks <br>
        $string = preg_replace( '/<(p|a)[^>]*?>([^<\/]*)<\/\1>/', '$2', $string);
        return trim( $string );

    }

    /**
     * Get plugins to register to support view helper being tested
     *
     * @return array
     */
    abstract protected function getPlugins();

}
