<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 01.03.16
 * Time: 10:03
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordMainEntryPersonalName extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        $arr = [];

        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var string $aut */
        $aut = "";

        /** @var \File_MARC_Data_Field $field100 */
        $field100 = $marcRecord->getField('100');

        $a = $this->getSubFieldDataOfGivenField($field100, 'a');
        $b = $this->getSubFieldDataOfGivenField($field100, 'b');
        $c = $this->getSubFieldDataOfGivenField($field100, 'c');

        $aut .= $a ? $a : "";
        $aut .= $b ? " $b" : "";
        $aut .= $c ? " <$c>" : "";
        $arr[] = $this->authorSearchLink($aut);
        /** @var \File_MARC_Data_Field $field */
        foreach ($marcRecord->getFields('700') as $field) {
            if ($field->getSubfield('4')->getData() !== 'aut') {
                continue;
            }
            $aut = "";
            $a = $this->getSubFieldDataOfGivenField($field, 'a');
            $b = $this->getSubFieldDataOfGivenField($field, 'b');
            $c = $this->getSubFieldDataOfGivenField($field, 'c');
            $aut .= $a ? $a : "";
            $aut .= $b ? " $b" : "";
            $aut .= $c ? " <$c>" : "";
            $arr[] = $this->authorSearchLink($aut);
        }

        return implode("; ", $arr);
    }

    private function authorSearchLink($author)
    {
        if (empty($author)) {
            return $author;
        }
        $href = sprintf(parent::URL_AUTHOR_SEARCH_PATTERN, urlencode(trim($author)));
        return $this->generateLink($href, $author, $author);
    }


}