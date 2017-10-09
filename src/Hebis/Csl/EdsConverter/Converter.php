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

namespace Hebis\Csl\EdsConverter;

use Hebis\Csl\Model\Record;
use Hebis\RecordDriver\ContentType;
use Hebis\RecordDriver\EDS;
use Hebis\RecordDriver\SolrMarc;

/**
 * Class Converter
 * @package Hebis\Csl\EdsConverter
 * @author
 */
class Converter
{

    public static function convert(EDS $record)
    {

        $type = $record->getPubType();

        switch ($type) {
            case 'featureArticle':
            case 'academicJournal':
            case 'serialPeriodical':
                return static::convertArticle($record);
            case 'musicalscore':
                return MusicalScoreConverter::convert($record->getMarcRecord());
            case 'map':
                return MapConverter::convert($record->getMarcRecord());
            case 'book':
            default:
                if (self::isThesis($record->getMarcRecord())) {
                    return ThesisConverter::convert($record->getMarcRecord());
                }
                return BookConverter::convert($record->getMarcRecord());
        }
    }

    public static function isThesis($marcRecord)
    {
        return !empty($marcRecord->getField("502"));
    }

    public static function convertArticle(EDS $record)
    {
        $article = new Record();
        $article->setType("article");
        //$article->setAuthor()
        return $article;
    }

    private static function getContentType($record)
    {
    }
}
