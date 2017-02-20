<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an
 * extension of the open source library search engine VuFind, that
 * allows users to search and browse beyond resources. More
 * Information about VuFind you will find on http://www.vufind.org
 *
 * Copyright (C) 2017
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

namespace Hebis\Csl\MarcConverter;

use Hebis\Csl\Model\Record as ConferencePaper;

class PaperConferenceConverter
{

    public static function convert(\File_MARC_Record $record)
    {
        $confPaper = new ConferencePaper();
        $confPaper->setAuthority(self::getAuthority($record));
        $confPaper->setCollectionNumber(Record::getCollectionNumber($record));
        $confPaper->setCollectionTitle(Record::getCollectionTitle($record));
        $confPaper->setContainerTitle(Record::getContainerTitle($record));
        $confPaper->setDOI(Record::getDOI($record));
        $confPaper->setEdition(Record::getEdition($record));
        $confPaper->setEditor(Name::getEditor($record));
        $confPaper->setEvent(self::getEvent($record));
    }

    private static function getAuthority(\File_MARC_Record $marcRecord)
    {
        $authorities = [];
        $marc100 = $marcRecord->getFields('111');
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $authorities[] = Name::extractName($field);
            }
        }
        $marc700 = $marcRecord->getFields('711');

        array_filter($marc700, function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            if (!empty($_4)) {
                return $ind2 == " ";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $authorities[] = Name::extractName($field);
            }
        }
        return $authorities;
    }

    private static function getEvent($record)
    {

    }
}