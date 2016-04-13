<?php
/**
 * This class is used for reading a Pica Record from a string
 *
 * PHP version 5
 */
namespace Hebis\RecordDriver;

use HAB\Pica\Parser\PicaPlainParser;
use HAB\Pica\Record\CopyRecord;
use HAB\Pica\Record\Field;
use HAB\Pica\Record\LocalRecord;
use HAB\Pica\Record\Record;
use HAB\Pica\Record\TitleRecord;

class PicaRecordParser
{

    /**
     * @var PicaRecordParser
     */
    private static $instance;


    /**
     * @var PicaRecord $picaRecord
     */
    protected $picaRecord;


    /**
     * @return PicaRecordParser
     */
    public static function getInstance()
    {
        if (self::$instance == null) {

            self::$instance = new PicaRecordParser();
        }

        return self::$instance;
    }

    /**
     * @see PicaRecordParser::getInstance
     * PicaRecordParser constructor.
     */
    private function __construct() { }

    /**
     *
     * @param string $rawPicaRecord
     * @return $this
     */
    public function parse($rawPicaRecord)
    {

        /*
         * split raw record in its levels (raw)
         */
        $recordLevels = explode("\n\n", $rawPicaRecord);

        $level = null;

        foreach ($recordLevels as $level) {

            /*
             * find record type
             */

            $recordLevel = null;
            $match = [];
            if (preg_match('/^(alg|lok|exp)/', $level, $match)) {
                $recordLevel = $match[1];
                //$ipn = $match[2];
            }

            /*
             * split level in its fields
             */
            $fieldsArray = explode("\n", $level);

            /*
             * parse lines to Pica Fields
             */
            $rawFields = array_slice($fieldsArray, 1, count($fieldsArray)-1);
            $record['fields'] = [];
            foreach ($rawFields as $rawField) {
                $record['fields'][] = PicaPlainParser::parseField($rawField);
            }

            if ($recordLevel === "alg") {

                /** @var TitleRecord $titleRecord */
                $this->picaRecord = PicaRecordFactory::factory($record); //level0 records via Record factory

            } else {
                $fieldObjectsArray = [];

                foreach ($record['fields'] as $field) {
                    $fieldObjectsArray[] = Field::factory($field); //parse each field separate
                }

                switch ($recordLevel) {
                    case 'lok':
                        $localRecord = new LocalRecord();
                        $localRecord->setFields($fieldObjectsArray);
                        $this->picaRecord->addLocalRecord($localRecord);
                        break;
                    case 'exp':
                        $copyRecord = new CopyRecord();
                        $copyRecord->setFields($fieldObjectsArray);
                        $this->picaRecord->getLocalRecords()[count($this->picaRecord->getLocalRecords())-1]
                            ->addCopyRecord($copyRecord);
                        break;
                    default:
                        //TODO: there are other level?
                }

            }
        }

        return $this;
    }

    public function getRecord()
    {
        return $this->picaRecord;
    }
}

