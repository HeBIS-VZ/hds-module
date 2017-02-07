<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.02.17
 * Time: 09:25
 */

namespace Hebis\Csl\MarcConverter;
use \Hebis\Csl\Model;

class Date extends Record
{

    /**
     * @param \File_MARC_Record $record
     * @return Model\Date|null
     */
    public static function getIssued(\File_MARC_Record $record)
    {
        $year = self::getSubfield($record, "264", "c");

        if (!empty($year)) {
            $date = new Model\Date();
            $date->setDateParts([[$year]]);
            $date->setLiteral($year);
            return $date;
        }
        return null;
    }

}