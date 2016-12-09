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

namespace Hebis\View\Helper\Record\SingleRecord;
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\ResultList\ResultListHostItemEntry;


/**
 * Class SingleRecordHostItemEntry
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordHostItemEntry extends ResultListHostItemEntry
{

    /**
     *
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        $id = $record->getUniqueID();
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /* 773 $a._$t._-_$b._-_$d._-_$g._-_$h._-_$z._-_$o._-_$x */

        /** @var \File_MARC_Data_Field $field */
        $fields = $marcRecord->getFields(773);
        $out = $this->output($fields);
        $w = $this->getAllAssociatedPPNs($fields);
        return !empty($w) ? implode("<br />", [$out, $this->showAllLink($record, $w[0])]) : $out;
    }

    protected function getAllAssociatedPPNs($fields) {
        $w = [];
        foreach ($fields as $field) {
            $w = array_merge($w, $this->getAssociatedPPNs($field));
        }
        return $w;
    }

    /**
     * @param \File_MARC_Subfield $subfield
     * @param \File_MARC_Subfield $w
     * @return string
     */
    protected function addLink($subfield, $w)
    {
        $view = $this->getView();

        $title = htmlentities($subfield->getData());
        $href = $view->basePath()."/".sprintf(parent::URL_SEARCH_PPN, $this->removePrefix($w->getData(), "(DE-603)"));

        return sprintf('<a href="%s" title="%s">%s</a>', $href, $title, $title);
    }

    protected function showAllLink($record, $w) {
        $view = $this->getView();
        $href = $view->record($record)->getLink('part_of', $this->removePrefix($w->getData(), "(DE-603)")); //$view->basePath()."/".sprintf(parent::URL_SHOW_ALL, $this->removePrefix($w->getData(), "(DE-603)"));
        $linkTitle = $view->transEsc('show all');
        return sprintf('<a href="%s" title="%s">%s</a>', $href, $linkTitle, $linkTitle);
    }


}