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

namespace Hebis\View\Helper\Record\Tab;

use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;


/**
 * Class TabToc
 * @package Hebis\View\Helper\Record\Tab
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class TabTocSummary extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $ret = [];

        $fields520 = $marcRecord->getFields(520);

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields520 as $field) {
            $a = $this->getSubField($field, "a");
            $ret[] = str_replace("Abstract-Anfang", "", $a);
        }

        $fields856 = array_filter($marcRecord->getFields(856), function($field) {
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(2) == 2;
        });
        foreach ($fields856 as $field) {
            $u = $this->getSubField($field, "u");
            $_3 = $this->getSubField($field, "3");

            if (!empty($u) && !empty($_3) && $_3 !== "Umschlagbild" && $_3 !== "Cover") {
                $ret[] = '<a href="' . $u . '">' . htmlentities($_3) . '</a>';
            } else {
                if (!empty($u) && empty($_3)) {
                    $ret[] = '<a href="' . $u . '">' . $this->getView()->transEsc("tab_description_note_about_content") . '</a>';
                }
            }
        }
        return implode("<br />\n", $ret);
    }
}