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
namespace Hebis\View\Helper\Record\BibTip;


use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;

/**
 * Class BibTipPublication
 * @package Hebis\View\Helper\Record\BibTip
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class BibTipPublication extends AbstractRecordViewHelper
{

    public function __invoke(SolrMarc $record)
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $record->getMarcRecord();

        $_533_d = $this->getSubFieldDataOfField($record, 533, 'd');

        if (!empty($_533_d)) {
            return $_533_d;
        }

        $_264__ = $marcRecord->getFields(264);
        $_264_ = $this->filterByIndicator($_264__, 2, "1");

        usort($_264_, function (\File_MARC_Data_Field $a, \File_MARC_Data_Field $b) {
            return $a->getIndicator(1) > $b->getIndicator(1) ? -1 : 1;
        });

        if (!empty($_264_)) {
            $a = current($_264_);
            if (!empty($c = $a->getSubfield('c'))) {
                return $c->getData();
            }
        }

        return "";
    }
}