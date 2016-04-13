<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 03.03.16
 * Time: 17:31
 */

namespace Hebis\View\Helper;

use Hebis\RecordDriver\SolrMarc;

abstract class AbstractViewHelperTest extends \VuFindTest\Unit\ViewHelperTestCase
{

    const VIEW_HELPER_NAMESPACE = "\\Hebis\\View\\Helper";

    /**
     * @var array;
     */
    protected $testRecordIds;

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

    public function setUp()
    {
        $this->viewHelper = self::factory($this->viewHelperClass);
        $this->viewHelper->setView($this->getPhpRenderer($this->getPlugins()));
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
        echo "\n";
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
