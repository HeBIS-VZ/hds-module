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

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Config\Config;
use Zend\Session\Container;

/**
 * Class UserOAuth
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class UserOAuth extends Gateway
{

    private $config;
    private $session;

    /**
     * Constructor
     *
     * @param Config $config VuFind configuration
     * @param string $rowClass Name of class for representing rows
     * @param Container $session Session container to inject into rows (optional;
     * used for privacy mode)
     */
    public function __construct(
        Config $config,
        $rowClass = 'VuFind\Db\Row\UserOAuth',
        Container $session = null
    ) {
        parent::__construct('user_oauth', $rowClass);
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * Create a row for the specified username.
     *
     * @param string $username Username to use for retrieval.
     *
     * @return object
     */
    public function createRowForUsername($username)
    {
        $row = $this->createRow();
        $row->username = $username;
        $row->created = date('Y-m-d H:i:s');
        $date = date_create('2006-12-12');
        date_modify($date, '-1 day');
        $row->expires = date_format($date, 'Y-m-d');
        return $row;
    }


    /**
     * Retrieve a user object from the database based on username; when requested,
     * create a new row if no existing match is found.
     *
     * @param string $username Username to use for retrieval.
     * @param bool $create Should we create users that don't already exist?
     *
     * @return object
     */
    public function getByUsername($username, $create = true)
    {
        $row = $this->select(['username' => $username])->current();
        return ($create && empty($row))
            ? $this->createRowForUsername($username) : $row;
    }
}
