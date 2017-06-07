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

namespace Hebis\RecordTab;

use Hebis\RecordDriver\SolrMarc;

/**
 * Interface TabInterface
 * @package Hebis\RecordTab
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class Description extends \VuFind\RecordTab\Description implements TabInterface
{

    public function hasContents()
    {
        $record = $this->getRecordDriver();
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();
        return
            $this->has($marcRecord, 510, "a") ||
            $this->has($marcRecord, 770) ||
            $this->has($marcRecord, 772) ||
            $this->has($marcRecord, 787) ||
            $this->has($marcRecord, 500) ||
            $this->has($marcRecord, 501) ||
            $this->has($marcRecord, 504) ||
            $this->has($marcRecord, 511) ||
            $this->has($marcRecord, 515) ||
            $this->has($marcRecord, 518) ||
            $this->has($marcRecord, 538) ||
            $this->has($marcRecord, 546) ||
            $this->has($marcRecord, 550) ||
            $this->has($marcRecord, 555) ||
            $this->has($marcRecord, 583);
    }

    /**
     * @param \File_MARC_Record $marcRecord
     * @param $fieldCode
     * @param $subFieldCode
     * @return bool
     */
    private function has($marcRecord, $fieldCode, $subFieldCode = null)
    {
        $fields = $marcRecord->getFields($fieldCode);

        if (empty($fields)) {
            return false;
        } else {
            if ($subFieldCode === null) {
                return true;
            }
        }

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields as $field) {
            $subField = $field->getSubfields($subFieldCode);
            if (!empty($subField)) {
                return true;
            }
        }

        return false;
    }
}

