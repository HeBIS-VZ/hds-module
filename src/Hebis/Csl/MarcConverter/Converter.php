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
            case 'book':
                return BookConverter::convert($record->getMarcRecord());
            case '':


            default:

        }
    }
}