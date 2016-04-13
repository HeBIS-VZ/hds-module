<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 23.02.16
 * Time: 17:52
 */

namespace Hebis\RecordDriver;

use HAB\Pica\Record\Field;
use HAB\Pica\Record\AuthorityRecord;
use \InvalidArgumentException;

class PicaRecordFactory
{
    /**
     * @param $record
     * @return AuthorityRecord|PicaRecord
     * @throws InvalidArgumentException
     */
    public static function factory($record)
    {
        if (!array_key_exists('fields', $record)) {
            throw new InvalidArgumentException("Missing 'fields' index in record array");
        }

        $fields = array_map(array('HAB\Pica\Record\Field', 'factory'), $record['fields']);
        $type = null;

        /** @var Field $field */
        foreach ($fields as $field) {
            if (Field::match('002@/00',$field)) {
                $typeSubField = $field->getNthSubField('0', 0);
                if ($typeSubField) {
                    $type = $typeSubField->getValue();
                    break;
                }
            }
        }

        if ($type === null) {
            throw new InvalidArgumentException("Missing type field (002@/00$0)");
        }

        if ($type[0] === 'T') {
            return new AuthorityRecord($fields);
        } else {
            return new PicaRecord($fields);
        }
    }

}