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

namespace Hebis\RecordDriver;


use HAB\Pica\Record\Field;
use HAB\Pica\Record\LocalRecord;
use HAB\Pica\Record\SubField;
use HAB\Pica\Record\TitleRecord;

/**
 * Class InterimPicaRecordInterface
 * @package Hebis\RecordDriver
 */
class PicaRecord extends TitleRecord implements PicaRecordInterface
{

    /**
     * @var array $journal
     */
    protected $journal = [];

    /**
     * @var array
     */
    protected $volumes = [];

    /**
     * @var array
     */
    protected $retroUrl = [];

    /**
     * returns an associative array containing the following keys:
     * <ul>
     *  <li>ppn</li>
     *  <li>prefix</li>
     *  <li>name</li>
     *  <li>band</li>
     *  <li>kommentar</li>
     *  <li>seite</li>
     * </ul>
     *
     * @return array
     */
    public function getJournal()
    {

        if (empty($this->journal)) { //build journal array only once, on the first access (lazy loading)

            /** @var Field $field039B */
            $field039B = $this->getFields('039B')[0];

            if (!empty($field039B->getNthSubField('9', 0))) {
                $this->journal['ppn'] = $field039B->getNthSubField('9', 0);
            }

            if (!empty($field039B->getNthSubField('a', 0))) {
                $this->journal['prefix'] = $field039B->getNthSubField('9', 0);
            }

            if (!empty($field039B->getNthSubField('8', 0))) {
                $pattern1 = '/--.+--:/';
                $pattern2 = '/--.+--/';
                $this->journal['name'] = preg_replace($pattern2, '', preg_replace($pattern1, '', $field039B->getNthSubField('8', 0)));
            }

            if (!empty($field039B->getNthSubField('c', 0)) && empty($this->journal['name'])) {
                $this->journal['name'] = $field039B->getNthSubField('c', 0);
            }

            /** @var Field $field031A */
            $field031A = $this->getFields('031A')[0];

            if (!empty($field031A->getNthSubField('d', 0))) {
                $this->journal['band'] = $field031A->getNthSubField('d', 0);
            }

            if (!empty($field031A->getNthSubField('j', 0))) {
                $this->journal['jahr'] = $field031A->getNthSubField('j', 0);
            }

            if (!empty($field031A->getNthSubField('e', 0))) {
                $this->journal['kommentar'] = $field031A->getNthSubField('e', 0);
            }

            if (!empty($field031A->getNthSubField('h', 0))) {
                $this->journal['seite'] = $field031A->getNthSubField('h', 0);
            }

        }

        return $this->journal;
    }

    public function getVolumes()
    {
        /** @var Field $field002at */
        $field002at = $this->getFields('002@')[0];

        if (empty($this->volumes)) {
            /** @var SubField $subField0 */
            $subField0 = $field002at->getNthSubField('0', 0);
            if (strpos($subField0, "c") === 1 || strpos($subField0, "d") === 1) {
                $this->volumes[(string)$subField0] = 'allvolumes';
            }
        }
        return $this->volumes;
    }

    public function getSeries()
    {
        // TODO: Implement getSeries() method.
    }

    public function getReviewed()
    {
        // TODO: Implement getReviewed() method.
    }

    public function getReview()
    {
        // TODO: Implement getReview() method.
    }

    public function getCopies()
    {
        /** @var LocalRecord $tmp */
        $tmp = $this->getLocalRecords()[0];
        return $tmp->getCopyRecords();
    }

    public function getCopiesFromILN($iln)
    {
        return $this->getLocalRecordByILN($iln)->getCopyRecords();
    }

    /**
     * @return array
     */
    public function getRetroUrl()
    {
        if (empty($this->retroUrl)) {
            /** @var Field $field009R */
            $field009R = $this->getFields('009R')[0];

            /** @var Field $field002at */
            $field002at = $this->getFields('002@')[0];

            if (!empty($field009R->getNthSubField('u', 0) && strpos($field002at->getNthSubField('0', 0), "r" === 0))) {
                $this->retroUrl = [
                    $field009R->getNthSubField('u', 0),
                    $field002at->getNthSubField('0', 0)
                ];
            }
        }

        return $this->retroUrl;
    }
}