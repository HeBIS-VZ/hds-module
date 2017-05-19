<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 05.02.16
 * Time: 10:53
 */

namespace HebisTest\RecordDriver;

use Hebis\RecordDriver\PicaRecordInterface;
use Hebis\RecordDriver\PicaRecordParser;

class PicaRecordParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected $rawPicaRecord;

    public function setUp()
    {
        parent::setUp();
        //$picaFile = PHPUNIT_FIXTURES_HEBIS.'/data/pica/two.pica';
        $this->rawPicaRecord = $this->getFixture('advanced');
    }

    /**
     * @test
     * @covers Hebis\RecordDriver\Pica::decode
     */
    public function decodeTest()
    {
        $picaParser = PicaRecordParser::getInstance();
        $picaRecord = $picaParser->parse($this->rawPicaRecord)->getRecord();

        print_r($picaRecord);
    }

    /**
     * @param $fixture
     * @return string
     */
    protected function getFixture($fixture)
    {
        $fixturePath = \PHPUNIT_FIXTURES_HEBIS . DIRECTORY_SEPARATOR . "{$fixture}.pp";
        echo "$fixturePath\n";
        return file_get_contents(\PHPUNIT_FIXTURES_HEBIS . DIRECTORY_SEPARATOR . "{$fixture}.pp");
    }

}