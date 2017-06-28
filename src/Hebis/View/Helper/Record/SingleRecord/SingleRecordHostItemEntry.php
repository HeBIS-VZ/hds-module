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
use Hebis\View\Helper\Hebisbs3\MultipartItems;
use Hebis\View\Helper\Record\ResultList\ResultListHostItemEntry;


/**
 * Class SingleRecordHostItemEntry
 * @package Hebis\View\Helper\Record\SingleRecord
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordHostItemEntry extends ResultListHostItemEntry
{

    private $record;

    /**
     *
     * @param SolrMarc $record
     * @return string
     */
    public function __invoke(SolrMarc $record)
    {
        $this->record = $record;

        $id = $record->getUniqueID();
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        /* 773 $a._$t._-_$b._-_$d._-_$g._-_$h._-_$z._-_$o._-_$x */

        /** @var \File_MARC_Data_Field $field */
        $fields = $marcRecord->getFields(773);
        $out = $this->output($fields);
        $w = $this->getAllAssociatedPPNs($fields);
        $ret = !empty($w) && !empty($out) ? implode("<br />", [$out, $this->showAllLink($record, $w[0])]) : "";
        return $ret;
    }

    protected function getAllAssociatedPPNs($fields)
    {
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

        return $this->getView()->ppnLink()->getLink(
            htmlentities($subfield->getData()),
            $this->removePrefix($w->getData(), "(DE-603)"),
            ["backlink" => $this->record->getPPN()]
        );
    }

    protected function showAllLink($record, $w)
    {

        /** @var MultipartItems $viewHelper */
        $viewHelper = $this->getView()->multipartItems($record);
        return $viewHelper->renderShowAllVolumesLink();
    }


}