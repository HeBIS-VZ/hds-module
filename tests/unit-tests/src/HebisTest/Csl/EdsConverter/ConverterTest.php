<?php

namespace HebisTest\Csl\EdsConverter;

use Hebis\Csl\EdsConverter\Converter;
use Hebis\Csl\Model\Name;
use Hebis\RecordDriver\EDS;
use VuFindTest\Backend\EDS\BackendTest;
use \InvalidArgumentException;

class ConverterTest extends BackendTest
{

    const PHPUNIT_SEARCH_FIXTURES = APPLICATION_PATH . '/module/Hebis/tests/unit-tests/fixtures';

    protected $edsRecord;

    protected $config;

    public function setUp()
    {
        parent::setUp();

    }

    /**
     * Load a response as fixture.
     *
     * @param string $fixture Fixture file
     *
     * @return mixed
     *
     * @throws InvalidArgumentException Fixture files does not exist
     */
    protected function loadResponse($fixture)
    {
        $file = sprintf('%s/eds/response/%s', self::PHPUNIT_SEARCH_FIXTURES, $fixture);
        $file = realpath($file);
        if (!$file || !is_string($file) || !file_exists($file) || !is_readable($file)) {
            throw new InvalidArgumentException(sprintf('Unable to load fixture file: %s', $fixture));
        }
        return unserialize(file_get_contents($file));
    }

    public function getRecord($id)
    {
        $record = new EDS();
        $record->setFields($this->loadResponse($id.".eds"));
        return $record;
    }


    public function testConvertArticle()
    {
        $record = $this->getRecord("30h,61912725");
        $article = Converter::convert($record);
        $this->assertNotEmpty($article->getAuthor());
        $this->assertNotEmpty($article->getAuthor()[1]);
        $this->assertEquals($article->getAuthor()[0]->getFamily(), "Fedotov");
        $this->assertEquals($article->getPage(), "174-181");

    }


}
