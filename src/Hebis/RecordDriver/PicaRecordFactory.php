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
            if (Field::match('002@/00')) {
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