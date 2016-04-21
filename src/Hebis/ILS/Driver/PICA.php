<?php
/*
 * This file is a part of HDS (HeBIS Discovery System). HDS is an 
 * extension of the open source library search engine VuFind, that 
 * allows users to search and browse beyond resources. More 
 * Information about VuFind you will find on http://www.vufind.org
 * 
<<<<<<< HEAD
 * Copyright (C) 2016 
 * Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
=======
 * Copyright (C) 2016
>>>>>>> devel
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

namespace Hebis\ILS\Driver;
<<<<<<< HEAD
use VuFind\ILS\Driver\DAIA;
use DOMDocument;
use VuFind\Exception\ILS as ILSException;

=======

use DOMDocument;
use VuFind\ILS\Driver\DAIA;
>>>>>>> devel

/**
 * Class PICA
 * @package Hebis\ILS\Driver
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class PICA extends DAIA
{
    protected $username;
    protected $password;
    protected $ldapConfigurationParameter;
    protected $catalogHost;
    protected $renewalsScript;
    protected $dbsid;
    /**
     * Initialize the driver.
     *
     * Validate configuration and perform all resource-intensive tasks needed to
     * make the driver active.
     *
     * @throws ILSException
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->catalogHost = $this->config['Catalog']['Host'];
        $this->renewalsScript = $this->config['Catalog']['renewalsScript'];
        $this->dbsid = isset($this->config['Catalog']['DB'])
            ? $this->config['Catalog']['DB'] : 1;
    }
    // public functions implemented to satisfy Driver Interface
    /**
     * Patron Login
     *
     * This is responsible for authenticating a patron against the catalog.
     *
     * @param string $barcode  The patron username
     * @param string $password The patron's password
     *
     * @throws ILSException
     * @return mixed          Associative array of patron info on successful login,
     * null on unsuccessful login.
     */
    public function patronLogin($barcode, $password)
    {
        // Build request:
        $request = new \Zend\Http\Request();
        $request->getPost()
            ->set('username', $barcode)
            ->set('password', $password);
        // First try local database:
        $db = new \VuFind\Auth\Database();
        try {
            $user = $db->authenticate($request);
        } catch (\VuFind\Exception\Auth $e) {
            // Next try LDAP:
            $ldap = new \VuFind\Auth\LDAP();
            $user = $ldap->authenticate($request);
        }
        $_SESSION['picauser'] = $user;
<<<<<<< HEAD
        return [
=======
        return array(
>>>>>>> devel
            'id' => $user->id,
            'firstname' =>  $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'username' => $barcode,
            'password' => $password,
            'cat_username' => $barcode,
            'cat_password' => $password
<<<<<<< HEAD
        ];
=======
        );
>>>>>>> devel
    }
    /**
     * Get Patron Profile
     *
     * This is responsible for retrieving the profile for a specific patron.
     *
     * @param array $user The patron array
     *
     * @throws ILSException
     * @return array      Array of the patron's profile data on success.
     */
    public function getMyProfile($user)
    {
        // TODO: this object probably doesn't have enough fields; it may be necessary
        // to subclass VuFind\Auth\LDAP with a different processLDAPUser() method for
        // loading the additional required properties.
        $userinfo = & $_SESSION['picauser'];
        // firstname
        $recordList['firstname'] = $userinfo->firstname;
        // lastname
        $recordList['lastname'] = $userinfo->lastname;
        // email
        $recordList['email'] = $userinfo->email;
        //Street and Number $ City $ Zip
        if ($userinfo->address) {
            $address = explode("\$", $userinfo->address);
            // address1
            $recordList['address1'] = $address[1];
            // address2
            $recordList['address2'] = $address[2];
            // zip (Post Code)
            $recordList['zip'] = $address[3];
        } else if ($userinfo->homeaddress) {
            $address = explode("\$", $userinfo->homeaddress);
            $recordList['address2'] = $address[0];
            $recordList['zip'] = $address[1];
        }
        // phone
        $recordList['phone'] = $userinfo->phone;
        // group
        $recordList['group'] = $userinfo->group;
        if ($recordList['firstname'] === null) {
            $recordList = $user;
            // add a group
            $recordList['group'] = 'No library account';
        }
        $recordList['expiration'] = $userinfo->libExpire;
        $recordList['status'] = $userinfo->borrowerStatus;
        // Get the LOANS-Page to extract a message for the user
        $URL = "/loan/DB={$this->dbsid}/USERINFO";
<<<<<<< HEAD
        $POST = [
=======
        $POST = array(
>>>>>>> devel
            "ACT" => "UI_DATA",
            "LNG" => "DU",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
<<<<<<< HEAD
        ];
=======
        );
>>>>>>> devel
        $postit = $this->postit($URL, $POST);
        // How many messages are there?
        $messages = substr_count($postit, '<strong class="alert">');
        $position = 0;
        if ($messages === 2) {
            // ignore the first message (its only the message to close the window
            // after finishing)
            for ($n = 0; $n<2; $n++) {
                $pos = strpos($postit, '<strong class="alert">', $position);
                $pos_close = strpos($postit, '</strong>', $pos);
                $value = substr($postit, $pos+22, ($pos_close-$pos-22));
                $position = $pos + 1;
            }
            $recordList['message'] = $value;
        }
        return $recordList;
    }
    /**
     * Get Patron Transactions
     *
     * This is responsible for retrieving all transactions (i.e. checked out items)
     * by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @throws \VuFind\Exception\Date
     * @throws ILSException
     * @return array        Array of the patron's transactions on success.
<<<<<<< HEAD
     *
=======
>>>>>>> devel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMyTransactions($patron)
    {
        $URL = "/loan/DB={$this->dbsid}/USERINFO";
<<<<<<< HEAD
        $POST = [
=======
        $POST = array(
>>>>>>> devel
            "ACT" => "UI_LOL",
            "LNG" => "DU",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
<<<<<<< HEAD
        ];
=======
        );
>>>>>>> devel
        $postit = $this->postit($URL, $POST);
        // How many items are there?
        $holds = substr_count($postit, 'input type="checkbox" name="VB"');
        $iframes = $holdsByIframe = substr_count($postit, '<iframe');
<<<<<<< HEAD
        $ppns = [];
        $expiration = [];
        $transList = [];
        $barcode = [];
        $reservations = [];
        $titles = [];
=======
        $ppns = array();
        $expiration = array();
        $transList = array();
        $barcode = array();
        $reservations = array();
        $titles = array();
>>>>>>> devel
        if ($holdsByIframe >= $holds) {
            $position = strpos($postit, '<iframe');
            for ($i = 0; $i < $iframes; $i++) {
                $pos = strpos($postit, 'VBAR=', $position);
                $value = substr($postit, $pos+9, 8);
                $completeValue = substr($postit, $pos+5, 12);
                $barcode[] = $completeValue;
                $bc = $this->getPpnByBarcode($value);
                $ppns[] = $bc;
                $position = $pos + 1;
                $current_position = $position;
                $position_state = null;
                for ($n = 0; $n<6; $n++) {
                    $current_position = $this->strposBackwards(
                        $postit, '<td class="value-small">', $current_position-1
                    );
                    if ($n === 1) {
                        $position_reservations = $current_position;
                    }
                    if ($n === 2) {
                        $position_expire = $current_position;
                    }
                    if ($n === 4) {
                        $position_state = $current_position;
                    }
                    if ($n === 5) {
                        $position_title = $current_position;
                    }
                }
                if ($position_state !== null
                    && substr($postit, $position_state+24, 8) !== 'bestellt'
                ) {
                    $reservations[] = substr($postit, $position_reservations+24, 1);
                    $expiration[] = substr($postit, $position_expire+24, 10);
                    $renewals[] = $this->getRenewals($completeValue);
                    $closing_title = strpos($postit, '</td>', $position_title);
<<<<<<< HEAD
                    $titles[] = $completeValue . " " . substr(
=======
                    $titles[] = $completeValue." ".substr(
>>>>>>> devel
                            $postit, $position_title+24,
                            ($closing_title-$position_title-24)
                        );
                } else {
                    $holdsByIframe--;
                    array_pop($ppns);
                    array_pop($barcode);
                }
            }
            $holds = $holdsByIframe;
        } else {
            // no iframes in PICA catalog, use checkboxes instead
            // Warning: reserved items have no checkbox in OPC! They wont appear
            // in this list
            $position = strpos($postit, 'input type="checkbox" name="VB"');
            for ($i = 0; $i < $holds; $i++) {
                $pos = strpos($postit, 'value=', $position);
                $value = substr($postit, $pos+11, 8);
                $completeValue = substr($postit, $pos+7, 12);
                $barcode[] = $completeValue;
                $ppns[] = $this->getPpnByBarcode($value);
                $position = $pos + 1;
                $position_expire = $position;
                for ($n = 0; $n<4; $n++) {
                    $position_expire = strpos(
                        $postit, '<td class="value-small">', $position_expire+1
                    );
                }
                $expiration[] = substr($postit, $position_expire+24, 10);
                $renewals[] = $this->getRenewals($completeValue);
            }
        }
        for ($i = 0; $i < $holds; $i++) {
            if ($ppns[$i] !== false) {
<<<<<<< HEAD
                $transList[] = [
=======
                $transList[] = array(
>>>>>>> devel
                    'id'      => $ppns[$i],
                    'duedate' => $expiration[$i],
                    'renewals' => $renewals[$i],
                    'reservations' => $reservations[$i],
                    'vb'      => $barcode[$i],
                    'title'   => $titles[$i]
<<<<<<< HEAD
                ];
            } else {
                // There is a problem: no PPN found for this item... lets take id 0
                // to avoid serious error (that will just return an empty title)
                $transList[] = [
=======
                );
            } else {
                // There is a problem: no PPN found for this item... lets take id 0
                // to avoid serious error (that will just return an empty title)
                $transList[] = array(
>>>>>>> devel
                    'id'      => 0,
                    'duedate' => $expiration[$i],
                    'renewals' => $renewals[$i],
                    'reservations' => $reservations[$i],
                    'vb'      => $barcode[$i],
                    'title'   => $titles[$i]
<<<<<<< HEAD
                ];
=======
                );
>>>>>>> devel
            }
        }
        return $transList;
    }
    /**
     * Support method - reverse strpos.
     *
     * @param string $haystack String to search within
     * @param string $needle   String to search for
     * @param int    $offset   Search offset
     *
     * @return int             Offset of $needle in $haystack
     */
    protected function strposBackwards($haystack, $needle, $offset = 0)
    {
        if ($offset === 0) {
            $haystack_reverse = strrev($haystack);
        } else {
            $haystack_reverse = strrev(substr($haystack, 0, $offset));
        }
        $needle_reverse = strrev($needle);
        $position_brutto = strpos($haystack_reverse, $needle_reverse);
        if ($offset === 0) {
            $position_netto = strlen($haystack)-$position_brutto-strlen($needle);
        } else {
            $position_netto = $offset-$position_brutto-strlen($needle);
        }
        return $position_netto;
    }
    /**
<<<<<<< HEAD
     * Get the number of renewals
=======
     * get the number of renewals
>>>>>>> devel
     *
     * @param string $barcode Barcode of the medium
     *
     * @return int number of renewals, if renewals script has not been set, return
     * false
     */
    protected function getRenewals($barcode)
    {
        $renewals = false;
        if (isset($this->renewalsScript) === true) {
<<<<<<< HEAD
            $POST = [
                "DB" => '1',
                "VBAR" => $barcode,
                "U" => $_SESSION['picauser']->username
            ];
=======
            $POST = array(
                "DB" => '1',
                "VBAR" => $barcode,
                "U" => $_SESSION['picauser']->username
            );
>>>>>>> devel
            $URL = $this->renewalsScript;
            $postit = $this->postit($URL, $POST);
            $renewalsString = $postit;
            $pos = strpos($postit, '<span');
            $renewals = strip_tags(substr($renewalsString, $pos));
        }
        return $renewals;
    }
    /**
     * Renew item(s)
     *
     * @param string $recordId Record identifier
     *
     * @return bool            True on success
<<<<<<< HEAD
     *
=======
>>>>>>> devel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function renew($recordId)
    {
        /* TODO: rewrite this to use VuFind's standard renewMyItems() mechanism.
        $URL = "/loan/DB={$this->dbsid}/LNG=DU/USERINFO";
        $POST = array(
            "ACT" => "UI_RENEWLOAN",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
        );
        if (is_array($recordId) === true) {
            // TODO: fix this; something seems wrong with the logic
            foreach ($recordId as $rid) {
                array_push($POST['VB'], $recordId);
            }
        } else {
            $POST['VB'] = $recordId;
        }
        $this->postit($URL, $POST);
         */
        return true;
    }
    /**
     * Get Patron Fines
     *
     * This is responsible for retrieving all fines by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @throws \VuFind\Exception\Date
     * @throws ILSException
     * @return mixed        Array of the patron's fines on success.
<<<<<<< HEAD
     *
=======
>>>>>>> devel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMyFines($patron)
    {
        $URL = "/loan/DB={$this->dbsid}/LNG=DU/USERINFO";
<<<<<<< HEAD
        $POST = [
            "ACT" => "UI_LOC",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
        ];
        $postit = $this->postit($URL, $POST);
        // How many items are there?
        $holds = substr_count($postit, '<td class="plain"')/3;
        $fineDate = [];
        $description = [];
        $fine = [];
=======
        $POST = array(
            "ACT" => "UI_LOC",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
        );
        $postit = $this->postit($URL, $POST);
        // How many items are there?
        $holds = substr_count($postit, '<td class="plain"')/3;
        $fineDate = array();
        $description = array();
        $fine = array();
>>>>>>> devel
        $position = strpos($postit, '<td class="infotab2" align="left">Betrag<td>');
        for ($i = 0; $i < $holds; $i++) {
            $pos = strpos($postit, '<td class="plain"', $position);
            // first class=plain => description
            // length = position of next </td> - startposition
            $nextClosingTd = strpos($postit, '</td>', $pos);
            $description[$i] = substr($postit, $pos+18, ($nextClosingTd-$pos-18));
            $position = $pos + 1;
            // next class=plain => date of fee creation
            $pos = strpos($postit, '<td class="plain"', $position);
            $nextClosingTd = strpos($postit, '</td>', $pos);
            $fineDate[$i] = substr($postit, $pos+18, ($nextClosingTd-$pos-18));
            $position = $pos + 1;
            // next class=plain => amount of fee
            $pos = strpos($postit, '<td class="plain"', $position);
            $nextClosingTd = strpos($postit, '</td>', $pos);
            $fineString = substr($postit, $pos+32, ($nextClosingTd-$pos-32));
            $feeString = explode(',', $fineString);
            $feeString[1] = substr($feeString[1], 0, 2);
            $fine[$i] = (double) implode('', $feeString);
            $position = $pos + 1;
        }
<<<<<<< HEAD
        $fineList = [];
        for ($i = 0; $i < $holds; $i++) {
            $fineList[] = [
=======
        $fineList = array();
        for ($i = 0; $i < $holds; $i++) {
            $fineList[] = array(
>>>>>>> devel
                "amount"   => $fine[$i],
                "checkout" => "",
                "fine"     => $fineDate[$i] . ': ' .
                    utf8_encode(html_entity_decode($description[$i])),
                "duedate"  => ""
<<<<<<< HEAD
            ];
=======
            );
>>>>>>> devel
            // id should be the ppn of the book resulting the fine but there's
            // currently no way to find out the PPN (we have neither barcode nor
            // signature...)
        }
        return $fineList;
    }
    /**
     * Get Patron Holds
     *
     * This is responsible for retrieving all holds by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @throws \VuFind\Exception\Date
     * @throws ILSException
     * @return array        Array of the patron's holds on success.
<<<<<<< HEAD
     *
=======
>>>>>>> devel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMyHolds($patron)
    {
        $URL = "/loan/DB={$this->dbsid}/LNG=DU/USERINFO";
<<<<<<< HEAD
        $POST = [
            "ACT" => "UI_LOR",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
        ];
        $postit = $this->postit($URL, $POST);
        // How many items are there?
        $holds = substr_count($postit, 'input type="checkbox" name="VB"');
        $ppns = [];
        $creation = [];
=======
        $POST = array(
            "ACT" => "UI_LOR",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
        );
        $postit = $this->postit($URL, $POST);
        // How many items are there?
        $holds = substr_count($postit, 'input type="checkbox" name="VB"');
        $ppns = array();
        $creation = array();
>>>>>>> devel
        $position = strpos($postit, 'input type="checkbox" name="VB"');
        for ($i = 0; $i < $holds; $i++) {
            $pos = strpos($postit, 'value=', $position);
            $value = substr($postit, $pos+11, 8);
            $ppns[] = $this->getPpnByBarcode($value);
            $position = $pos + 1;
            $position_create = $position;
            for ($n = 0; $n<3; $n++) {
                $position_create = strpos(
                    $postit, '<td class="value-small">', $position_create+1
                );
            }
            $creation[]
                = str_replace('-', '.', substr($postit, $position_create+24, 10));
        }
        /* items, which are ordered and have no signature yet, are not included in
         * the for-loop getthem by checkbox PPN
         */
        $moreholds = substr_count($postit, 'input type="checkbox" name="PPN"');
        $position = strpos($postit, 'input type="checkbox" name="PPN"');
        for ($i = 0; $i < $moreholds; $i++) {
            $pos = strpos($postit, 'value=', $position);
            // get the length of PPN
            $x = strpos($postit, '"', $pos+7);
            $value = substr($postit, $pos+7, $x-$pos-7);
            // problem: the value presented here does not contain the checksum!
            // so its not a valid identifier
            // we need to calculate the checksum
            $checksum = 0;
<<<<<<< HEAD
            for ($i = 0; $i<strlen($value);$i++) {
=======
            for ($i=0; $i<strlen($value);$i++) {
>>>>>>> devel
                $checksum += $value[$i]*(9-$i);
            }
            if ($checksum%11 === 1) {
                $checksum = 'X';
            } else if ($checksum%11 === 0) {
                $checksum = 0;
            } else {
                $checksum = 11 - $checksum%11;
            }
<<<<<<< HEAD
            $ppns[] = $value . $checksum;
=======
            $ppns[] = $value.$checksum;
>>>>>>> devel
            $position = $pos + 1;
            $position_create = $position;
            for ($n = 0; $n<3; $n++) {
                $position_create = strpos(
                    $postit, '<td class="value-small">', $position_create+1
                );
            }
            $creation[]
                = str_replace('-', '.', substr($postit, $position_create+24, 10));
        }
        /* media ordered from closed stack is not visible on the UI_LOR page
         * requested above... we need to do another request and filter the
         * UI_LOL-page for requests
         */
<<<<<<< HEAD
        $POST_LOL = [
            "ACT" => "UI_LOL",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
        ];
=======
        $POST_LOL = array(
            "ACT" => "UI_LOL",
            "BOR_U" => $_SESSION['picauser']->username,
            "BOR_PW" => $_SESSION['picauser']->getCatPassword()
        );
>>>>>>> devel
        $postit_lol = $this->postit($URL, $POST_LOL);
        $requests = substr_count(
            $postit_lol, '<td class="value-small">bestellt</td>'
        );
        $position = 0;
        for ($i = 0; $i < $requests; $i++) {
            $position = strpos(
                $postit_lol, '<td class="value-small">bestellt</td>', $position+1
            );
            $pos = strpos($postit_lol, '<td class="value-small">', ($position-100));
            $nextClosingTd = strpos($postit_lol, '</td>', $pos);
            $value = substr($postit_lol, $pos+27, ($nextClosingTd-$pos-27));
            $ppns[] = $this->getPpnByBarcode($value);
            $creation[] = date('d.m.Y');
        }
        for ($i = 0; $i < ($holds+$moreholds+$requests); $i++) {
<<<<<<< HEAD
            $holdList[] = [
                "id"       => $ppns[$i],
                "create"   => $creation[$i]
            ];
=======
            $holdList[] = array(
                "id"       => $ppns[$i],
                "create"   => $creation[$i]
            );
>>>>>>> devel
        }
        return $holdList;
    }
    /**
     * Place Hold
     *
     * Attempts to place a hold or recall on a particular item and returns
     * an array with result details or throws an exception on failure of support
     * classes
     *
     * @param array $holdDetails An array of item and patron data
     *
     * @throws ILSException
     * @return mixed An array of data on the request including
     * whether or not it was successful and a system message (if available)
     */
    //public function placeHold($holdDetails)
    //{
    //}
    /**
     * Get Funds
     *
     * Return a list of funds which may be used to limit the getNewItems list.
     *
     * @throws ILSException
     * @return array An associative array with key = fund ID, value = fund name.
     */
    public function getFunds()
    {
        // TODO
<<<<<<< HEAD
        return [];
    }
    // protected functions to connect to PICA
    /**
     * Post something to a foreign host
=======
        return array();
    }
    // protected functions to connect to PICA
    /**
     * post something to a foreign host
>>>>>>> devel
     *
     * @param string $file         POST target URL
     * @param string $data_to_send POST data
     *
     * @return string              POST response
     */
    protected function postit($file, $data_to_send)
    {
        // TODO: can we use Zend\Http\Client here instead?
        // Parameter verarbeiten
        foreach ($data_to_send as $key => $dat) {
            $data_to_send[$key]
<<<<<<< HEAD
                = "$key=" . rawurlencode(utf8_encode(stripslashes($dat)));
=======
                = "$key=".rawurlencode(utf8_encode(stripslashes($dat)));
>>>>>>> devel
        }
        $postData = implode("&", $data_to_send);
        // HTTP-Header vorbereiten
        $out  = "POST $file HTTP/1.1\r\n";
        $out .= "Host: " . $this->catalogHost . "\r\n";
        $out .= "Content-type: application/x-www-form-urlencoded\r\n";
<<<<<<< HEAD
        $out .= "Content-length: " . strlen($postData) . "\r\n";
        $out .= "User-Agent: " . $_SERVER["HTTP_USER_AGENT"] . "\r\n";
=======
        $out .= "Content-length: ". strlen($postData) ."\r\n";
        $out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
>>>>>>> devel
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";
        $out .= $postData;
        if (!$conex = @fsockopen($this->catalogHost, "80", $errno, $errstr, 10)) {
            error_log($errno . ': ' . $errstr);
            return 0;
        }
        fwrite($conex, $out);
        $data = '';
        while (!feof($conex)) {
            $data .= fgets($conex, 512);
        }
        fclose($conex);
        return $data;
    }
    /**
<<<<<<< HEAD
     * Gets a PPN by its barcode
=======
     * gets a PPN by its barcode
>>>>>>> devel
     *
     * @param string $barcode Barcode to use for lookup
     *
     * @return string         PPN
     */
    protected function getPpnByBarcode($barcode)
    {
        $searchUrl = "http://" . $this->catalogHost .
            "/DB={$this->dbsid}/XML=1.0/CMD?ACT=SRCHA&IKT=1016&SRT=YOP&TRM=sgn+" .
            $barcode;
        $doc = new DOMDocument();
        $doc->load($searchUrl);
        // get Availability information from DAIA
        $itemlist = $doc->getElementsByTagName('SHORTTITLE');
        if (count($itemlist->item(0)->attributes) > 0) {
            $ppn = $itemlist->item(0)->attributes->getNamedItem('PPN')->nodeValue;
        } else {
            return false;
        }
        return $ppn;
    }
    /**
<<<<<<< HEAD
     * Gets holdings of magazine and journal exemplars
=======
     * gets holdings of magazine and journal exemplars
>>>>>>> devel
     *
     * @param string $ppn PPN identifier
     *
     * @return array
     */
    public function getJournalHoldings($ppn)
    {
        $searchUrl = "http://" . $this->catalogHost .
            "/DB={$this->dbsid}/XML=1.0/SET=1/TTL=1/FAM?PPN=" . $ppn .
            "&SHRTST=10000";
        $doc = new DOMDocument();
        $doc->load($searchUrl);
        $itemlist = $doc->getElementsByTagName('SHORTTITLE');
<<<<<<< HEAD
        $ppn = [];
=======
        $ppn = array();
>>>>>>> devel
        for ($n = 0; $itemlist->item($n); $n++) {
            if (count($itemlist->item($n)->attributes) > 0) {
                $ppn[] = $itemlist->item($n)->attributes->getNamedItem('PPN')
                    ->nodeValue;
            }
        }
        return $ppn;
    }
}