<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 03.03.16
 * Time: 11:34
 */

namespace Hebis\View\Helper;


use Hebis\RecordDriver\SolrMarc;

class SingleRecordMarcJournal extends AbstractRecordViewHelper
{
    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /** @var string $leader */
        $leader = $marcRecord->getLeader();

        $ret = '';

        if ($leader[19] === ' ') {
            $fields = $marcRecord->getFields('773');

            /** @var \File_MARC_Data_Field $field */
            foreach ($fields as $i => $field) {
                $a = $this->getSubFieldDataOfGivenField($field, 'a');
                $t = $this->getSubFieldDataOfGivenField($field, 't');
                $g = $this->getSubFieldDataOfGivenField($field, 'g');

                $w = $this->getSubFieldDataOfGivenField($field, 'w'); // ppn with prefix (DE-603)

                if ($w) {
                    if ($a) {
                        $a = '<a href="'.$this->getView()->basePath().'/Search/Results?lookfor=HEB' .
                            $this->removePrefix($w, '(DE-603)') .
                            '&amp;type=id">'.htmlentities($a).'</a>';
                    }

                    if ($t) {
                        $t = '<a href="'.$this->getView()->basePath().'/Search/Results?lookfor=HEB' .
                            $this->removePrefix($w, '(DE-603)') .
                            '&amp;type=id">' . htmlentities($t) . '</a>';
                    }
                }

                $ret .= $a ? "$a: " : "";
                $ret .= $t ? "$t" : "";
                $ret .= $g ? ", $g" : "";
            }

            //TODO: Move in a separate ViewHelper
            /*
            $ret .= '<p>' .
                    '<a href="' .
                    $this->getView()->basePath() . '/Search/Results?lookfor=HEB' .
                    $this->removePrefix($_773_w, '(DE-603)') . '&amp;type=part_of&sort=pub_date_max+desc">' .
                    $this->getView()->transEsc('alle Artikel anzeigen') .
                    '</a></p>';
            */
            return $ret;
        }

    }
}