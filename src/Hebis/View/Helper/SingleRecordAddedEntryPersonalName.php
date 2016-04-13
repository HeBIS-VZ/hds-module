<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.03.16
 * Time: 10:05
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordAddedEntryPersonalName extends AbstractRecordViewHelper
{

    public function authorSearchLink($author)
    {
        if (empty($author)) {
            return $author;
        }
        $href = sprintf(parent::URL_AUTHOR_SEARCH_PATTERN, urlencode(trim($author)));
        return parent::generateLink($href, $author, $author);
    }

    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $fields = array_filter($marcRecord->getFields('700'), function(\File_MARC_Data_Field $field) {
            $subField = $field->getSubfield('4');
            return !empty($subField) && !in_array($subField->getData(), ['aut', 'hnr', 'prf']);
        });

        return implode(" ; ", $this->extractContents($fields));
    }

    /**
     * @param $fields
     * @return array
     */
    protected function extractContents($fields)
    {
        $arr = [];

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {

            /** @var string $ret */
            $ret = "";

            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $b = $this->getSubFieldDataOfGivenField($field, 'b');
            $c = $this->getSubFieldDataOfGivenField($field, 'c');
            $e = $this->getSubFieldDataOfGivenField($field, 'e');

            $ret .= $a ? $a : "";
            $ret .= $b ? " $b" : "";
            $ret .= $c ? " <$c>" : "";

            $ret .= $e ? " ($e)" : "";

            $arr[] = $this->authorSearchLink($ret);

        }

        return $arr;
    }

}