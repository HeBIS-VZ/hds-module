<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.02.17
 * Time: 13:36
 */
namespace Hebis\Csl\MarcConverter;

use Hebis\Csl\MarcConverter\Constraints\LeaderPosition;
use \Hebis\Csl\Model\Record as Book;

class BookConverter
{
    use SubfieldsTrait;

    public static function convert(\File_MARC_Record $record)
    {
        $book = new Book();

        $book->setAuthor(Name::getAuthor($record));
        $book->setAuthority(Name::getAuthority($record));
        $book->setCollectionNumber(self::getCollectionNumber($record));
        $book->setCollectionTitle(self::getCollectionTitle($record));
        $book->setContainerTitle(self::getContainerTitle($record));
        $book->setEditor(Name::getEditor($record));
        $book->setIllustrator(Name::getIllustrator($record));
        $book->setISBN(Record::getISBN($record));
        $book->setISSN(Record::getISSN($record));

        return $book;
    }

    private static function getCollectionNumber(\File_MARC_Record $record)
    {
        $collectionNumber = null;
        $leader = $record->getLeader();
        if ($leader{19} === "a") {
            $collectionNumber = self::getSubfield($record, "490", "v", function($field){
                /** @var \File_MARC_Data_Field $field */
                return $field->getIndicator(1) == "1";
            });
        }

        if ($leader{19} === "c") {
            $collectionNumber = self::getSubfield($record, "245", "n");
        }
        return $collectionNumber;
    }

    private static function getCollectionTitle(\File_MARC_Record $record)
    {
        $collectionTitle = null;
        $leader = $record->getLeader();
        if ($leader{19} === "a") {
            $collectionTitle = self::getSubfield($record, "490", "a", function($field){
                /** @var \File_MARC_Data_Field $field */
                return $field->getIndicator(1) == "1";
            });
        }
        if ($leader{19} === "c") {
            $collectionTitle = self::getSubfield($record, "245", "a", function($field){
                /** @var \File_MARC_Data_Field $field */
                return $field->getIndicator(1) == "1";
            });
        }
        return $collectionTitle;
    }

    private static function getContainerTitle(\File_MARC_Record $record)
    {
        return self::getSubfield($record, "490", "a", function($field){
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(1) == "0";
        });
    }

    public function bla(\File_MARC_Record $record)
    {
        $leader = $record->getLeader();

        $this->and(
            LeaderPosition::get($leader,  6)->match(    ['a','b']    ),
            LeaderPosition::get($leader,  7)->match(  ['m','i','s']  ),
            LeaderPosition::get($leader, 19)->match([' ','a','b','c'])
        );

        /** @var \File_MARC_Control_Field $field007 */
        $field007 = $record->getField("007");
        $data007 = $field007->getData();

    }

    /**
     * @return bool
     */
    public function and()
    {
        $numArgs = func_num_args();
        $args = func_get_Args();
        $ret = true;
        if ($numArgs > 0) {
            foreach ($args as $arg) {
                if (is_bool($arg)) {
                    if ($arg !== true) {
                        return false;
                    }
                    $ret = $ret && $arg;
                } else {
                    throw new \InvalidArgumentException("Boolean value needed for logical combination.");
                }
            }
        }
        return $ret;
    }
}