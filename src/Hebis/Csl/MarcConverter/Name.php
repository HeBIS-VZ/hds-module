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

use Hebis\Csl\Model;

class Name
{
    use SubfieldsTrait;

    /**
     * @param \File_MARC_Record $marcRecord
     * @return array
     */
    public static function getAuthor(\File_MARC_Record $marcRecord)
    {
        $authors = [];
        $marc100 = $marcRecord->getFields('100');
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $authors[] = self::extractName($field);
            }
        }
        $marc700 = $marcRecord->getFields('700');

        array_filter($marc700, function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "aut" && $ind2 == " ";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $authors[] = self::extractName($field);
            }
        }
        return $authors;
    }

    public static function extractName(\File_MARC_Data_Field $field)
    {
        $name = new Model\Name();
        $a = $field->getSubfield('a');
        $b = $field->getSubfield('b');
        $c = $field->getSubfield('c');

        if (!empty($a)) {
            $autstr = $a->getData();
            $a_ = explode(", ", $autstr);
            $name->setFamily($a_[0]);
            if (count($a_) > 1) {
                array_shift($a_);
                $name->setGiven(implode(" ", $a_));
            }

        }

        if (!empty($b)) {
            $name->setSuffix($b->getData());
        }

        if (!empty($c)) {
            $name->setFamily($name->getFamily() . " [" . $c->getData() . "]");
        }

        return $name;
    }

    public static function getEditor(\File_MARC_Record $marcRecord)
    {
        $editor = [];
        $marc100 = $marcRecord->getFields('100');
        array_filter($marc100, function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "edt" && $ind2 == " ";
            }
            return false;
        });
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $editor[] = self::extractName($field);
            }
        }
        $marc700 = $marcRecord->getFields('700');

        array_filter($marc700, function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "edt" && $ind2 == " ";
            }
            return false;
        });

        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $editor[] = self::extractName($field);
            }
        }
        return $editor;
    }

    public static function getIllustrator(\File_MARC_Record $marcRecord)
    {
        $filterIll = function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "ill";
            }
            return false;
        };

        $illustrator = [];
        $marc100 = $marcRecord->getFields('100');
        array_filter($marc100, $filterIll);
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $illustrator[] = self::extractName($field);
            }
        }

        $marc700 = $marcRecord->getFields('700');
        array_filter($marc700, function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $ind = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "ill" && $ind == " ";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $illustrator[] = self::extractName($field);
            }
        }
        return $illustrator;
    }

    public static function getAuthority($record)
    {
        return null;
    }

    public static function getTranslator(\File_MARC_Record $record)
    {
        $translators = $record->getFields("700");

        array_filter($translators, function ($field) {
            /** @var \File_MARC_Data_Field $field */
            $_4 = $field->getSubfield('4');
            return $field->getIndicator(2) === " " && $_4->getData() === "trl";
        });

        $names = [];
        foreach ($translators as $translator) {
            $names[] = self::extractName($translator);
        }
        return $names;
    }

    public static function getComposer(\File_MARC_Record $marcRecord)
    {
        $composers = [];
        $marc100 = $marcRecord->getFields('100');
        if (!empty($marc100)) {
            foreach ($marc100 as $field) {
                $composers[] = self::extractName($field);
            }
        }
        $marc700 = $marcRecord->getFields('700');

        array_filter($marc700, function ($field) {
            /** @var $field \File_MARC_Data_Field */
            $ind2 = $field->getIndicator(2);
            $_4 = $field->getSubfield(4);
            if (!empty($_4)) {
                return $_4->getData() === "cmp" && $ind2 == " ";
            }
            return false;
        });


        if (!empty($marc700)) {
            foreach ($marc700 as $field) {
                $composers[] = self::extractName($field);
            }
        }
        return $composers;
    }
}