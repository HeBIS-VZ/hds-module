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

namespace Hebis\View\Helper\Hebisbs3;

use Hebis\Exception\HebisException;
use Hebis\RecordDriver\ContentType;
use Hebis\RecordDriver\SolrMarc;
use Zend\ServiceManager\ServiceManager;
use Zend\Uri\Uri;

/**
 * Class MultipartItems
 * @package Hebis\View\Helper\Hebisbs3
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class MultipartItems extends \Zend\View\Helper\AbstractHelper
{

    private $sm;

    private $isMultipartItem;

    /**
     * @var SolrMarc
     */
    private $driver;

    /**
     * MultipartItems constructor.
     * @param ServiceManager $sm
     */
    public function __construct(ServiceManager $sm)
    {
        $this->sm = $sm;
    }

    /**
     * Store a record driver object and return this object so that the appropriate
     * template can be rendered.
     *
     * @param \Hebis\RecordDriver\SolrMarc $driver Record driver object.
     *
     * @return MultipartItems
     */
    public function __invoke($driver)
    {
        $this->driver = $driver;
        $this->isMultipartItem = ContentType::getContentType($this->driver) === "hierarchy";
        return $this;
    }

    /**
     * @return bool
     */
    public function isMultipartItem()
    {
        return $this->isMultipartItem;
    }

    public function renderShowAllVolumesLink()
    {
        if ($this->isMultipartItem) {
            $ppn = substr($this->driver->getPPN(), 3);
        } else {

            /** @var \File_MARC_Record $marcRecord */
            $marcRecord = $this->driver->getMarcRecord();

            /** @var \File_MARC_Data_Field $_773 */
            $_773 = $marcRecord->getField(773);
            if (!empty($_773)) {
                $w = $_773->getSubfield("w");
                if (!empty($w) && !empty($w->getData())) {
                    $ppn = substr($w->getData(), 8);
                }
            }
        }

        if (empty($ppn)) {
            throw new HebisException("Invalid state. No PPN present to generate link");
        }

        $linkText = $this->getView()->transEsc('show_all_volumes');

        $uri = new Uri($this->getView()->url('search-results'));

        $uri->setQuery(str_replace('+', '%20', http_build_query(
            ["sort" => "relevance",
             "type0[]" => "part_of",
             "lookfor0[]" => $ppn,
             "join" => "AND"]
        )));

        return '<a href="'.$uri->toString().'">'.$linkText.'</a>';

    }
}