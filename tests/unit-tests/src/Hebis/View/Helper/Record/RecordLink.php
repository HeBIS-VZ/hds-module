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

namespace Hebis\View\Helper\Record;
use VuFind\View\Helper\Root\RecordLink as Link;


/**
 * Class RecordLink
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class RecordLink extends Link
{

    public function __construct(\VuFind\Record\Router $router) {
        parent::__construct($router);
    }

    public function recordFinderProxy($ppn)
    {
        $this->getActionUrl("record_finder", "home", ['id' => "HEB".$ppn]);
    }

    public function getActionUrl($controller, $action, $params = [])
    {

        // Build the URL:
        $urlHelper = $this->getView()->plugin('url');

        $this->getView()->url()->fromRoute('route-name', $params);

        /*
        $this->router->getRouteDetails();
        $details = $this->router->getActionRouteDetails($driver, $action);
        return $urlHelper($details['route'], $params);
        */
    }
}