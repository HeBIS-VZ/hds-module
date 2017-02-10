<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 02.02.17
 * Time: 09:33
 */

namespace Hebis\Csl\MarcConverter;
use Hebis\RecordDriver\ContentType;
use Hebis\RecordDriver\SolrMarc;

class Converter
{

    public static function convert(SolrMarc $record)
    {

        $type = ContentType::getContentType($record);

        switch ($type) {
            case 'article':
                return ArticleConverter::convert($record->getMarcRecord());
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
}