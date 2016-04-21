<?php
/**
 *
 *
 * $Date:2014-01-13$
 *
 * Stub fuer einen ILS-Driver fuer PICA / HEBIS
 * basiert auf PICA-Vorlage von O.Mahrarens HH
 * 2012-05-23, -mp- 1. public Version
 * 2012-10-10, -mp- use SPs from PICA
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  ILS_Drivers for VuFind < 2.0
 * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_ils_driver Wiki
 */
/*
require_once 'Interface.php';
require_once 'DAIA.php';
require_once 'sys/User.php';
*/

namespace Hebis\ILS\Driver;

// PICA defined numbers for textline types
define("PICA_TEXTLINES_LOAN", "74");
define("PICA_TEXTLINES_FINES", "75");
define("PICA_TEXTLINES_DEPARTMENT", "114");

// Define for Driver Debug outputs
define("DRIVER_DEBUG", "deadbeaf");
use VuFind\Exception\ILS as ILSException;

class Hebis extends PICA
{
    // DATA for use in class
    private $link;
    private $picaBorrowerBar;
    private $picaAddressIdNr;
    private $picaIln;
    private $picaBorrowerStatus;
    private $picaGender;
    private $picaEmailAddress;
    private $picaBorrowerType;
    private $picaLanguageCode;
    private $picaReminderAddress;
    private $picaFno;
    private $picaLanguageOveride;
    private $picaFreeText;
    private $picaFreeTextBlock;
    private $picaPersonTitles;
    private $picaMessage;
    private $picaDBName;


    public function __construct()
    {
        //TODO: param $config missing

        $converter = new \VuFind\Date\Converter();
        parent::__construct($converter);
    }

    /* PRIVATE METHODES 
    *  utilities for PICA or Sybase etc.
    */

    /**
     * internal methode for renewing via POST directly in PICA
     *
     *
     * @param array $recordId volume_bar from PICA
     * @param array $password Pincode from user
     * @return string        results from POST-request
     * @access private
     */

    private function _renew($recordId, $password)
    {
        print_r("_renew($recordId , $password) <br>");
        $URL = "/loan/DB=" . $this->picaDBName . "/LNG=DU/USERINFO";
        $POST = array(
            "ACT" => "UI_RENEWLOAN",
            "BOR_U" => $this->picaBorrowerBar,
            "BOR_PW" => $password
        );
        if (is_array($recordId) === true) {
            foreach ($recordId as $rid) {
                array_push($POST['VB'], $recordId);
            }
        } else {
            $POST['VB'] = $recordId;
        }
        $postit = $this->_postit($URL, $POST);

        return $postit;
    }

    /**
     * internal methode for POST-reuqest
     *
     *
     * @param string $file URL to open
     * @param string $data body of the POST-request
     * @return string     results from POST-request
     * @access private
     */

    private function _postit($file, $dataToSend)
    {

        $client = new \Zend\Http\Client($this->catalogHost, [
            'maxredirects' => 3,
            'timeout' => 5
        ]);

        $client->setParameterPost($dataToSend);
        $response = $client->send();

        return $this->_picaRenewAnswer($response->getBody());

        // Parameter verarbeiten
        //print_r($data_to_send); # Zum Debuggen
        /*
        foreach ($data_to_send as $key => $dat) {
            $data_to_send[$key] = "$key=" . rawurlencode(utf8_encode(stripslashes($dat)));
        }
        *



        $postData = implode("&", $data_to_send);
        // HTTP-Header vorbereiten
        $out = "POST $file HTTP/1.1\r\n";
        $out .= "Host: " . $this->catalogHost . "\r\n";
        $out .= "Content-type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-length: " . strlen($postData) . "\r\n";
        $out .= "User-Agent: " . $_SERVER["HTTP_USER_AGENT"] . "\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";
        $out .= $postData;
        if (!$conex = @fsockopen($this->catalogHost, "80", $errno, $errstr, 10)) {
            echo $errno, $errstr;
            exit;
        }
        fwrite($conex, $out);
        $data = '';
        while (!feof($conex)) {
            $data .= fgets($conex, 512);
        }
        fclose($conex);
        return $this->_picaRenewAnswer($data);
        */
    }

    /**
     * internal methode for check on dates before "NOW"
     *
     *
     * @param string $date datetime from Sybase
     * @return boolean   true if input is in future
     * @access private
     */

    private function _picaIsExpired($date)
    {
        // 2013-05-27, -mp- + 1 Tag sonst nicht ma gleichen Tag verlaengerbar
        return (strtotime($date) + 86400 > time());
    }

    /**
     * internal methode checks PICA-user state (>2 == blocked)
     *
     * @return boolean   true if blocked
     * @access private
     */


    private function _picaUserStateBlocked()
    {
        // hard coded in PICA borrower_status >2 blocks
        return ($this->picaBorrowerStatus > 2) ? true : false;
    }


    /**
     * internal methode checks PICA regulations param 29
     * max number of renewals per media
     *
     * @param string $department deparment
     * @param string $renewals current renewals on media
     * @return boolean   true if lower than lower limit (OPAC)
     * @access private
     */

    private function _picaRegulations29($department, $renewals)
    {

        $sql_query = "select distinct regulations.iln,regulations.department_group_nr,regulations.parameter_id,regulations.number1,regulations.number2 from regulations,membershp where regulations.parameter_id=29 and membershp.address_id_nr=%s and regulations.iln=%s";
        $ret = mssql_query(sprintf($sql_query, $this->picaAddressIdNr, $this->picaIln), $this->_getLink());
        while ($row = mssql_fetch_array($ret)) {
            $maxRenews[] = array(
                'department' => $row['department_group_nr'],
                'param' => $row['parameter_id'],
                'lower' => $row['number1'],
                'upper' => $row['number2']
            );
        }
        foreach ($maxRenews as $key => $value) {
            if ($value['department'] == $department)
                return ($renewals < $value['lower'] - 1);
        }
    }


    /**
     * internal methode for scraping renewal messages from PICA via DOM
     * don't know how robust this is, needs HTML-Tag and CSS-class
     *
     * @param string $string HTML-Page containing PICA-answer while renewing
     * @return array with cancelDetails, not fully working on PICA
     * @access private
     */


    private function _picaRenewAnswer($string)
    {

        $html = new DOMDocument();
        $html->loadHTML($string);
        $td = null;
        foreach ($html->getElementsByTagName('td') as $table_data) {
            $table_data_array = array(
                'td' => $table_data->getAttribute('class'),
                'class' => $table_data->nodeValue,
            );

            // 2013-05-16, -mp- zur Unterscheidung geklappt / nicht geklappt
            // s.a. HERMES#LOANSERVER#HTML#LOAN_RENEWALS_REJECTED_DATA#
            if ($table_data_array['td'] == 'value-small error') {
                return 'ERR:' . $table_data_array['class'];
            }
            if ($table_data_array['td'] == 'value-small') {
                // bs in Marburg klappt das leider nicht immer mit der td class
                if (stripos($table_data_array['class'], 'Verweigerungsgrund') > 0) {
                    return 'ERR:' . $table_data_array['class'];
                }
                return 'OK:' . $table_data_array['class'];
            }
        }
        return false;
    }

    /**
     * internal methode gets department memberships
     *
     * @return array with deparments for current user
     * @access private
     */

    private function _picaMemberShips()
    {
        $sql_query = "select distinct membershp.expiry_date,textlines.* from textlines,department,membershp where textlines.fno=%s and textlines.typ=%s and textlines.number=department.department_group_nr and membershp.address_id_nr=%s";
        $ret = mssql_query(sprintf($sql_query, $this->picaFno, PICA_TEXTLINES_DEPARTMENT, $this->picaAddressIdNr), $this->_getLink());
        while ($row = mssql_fetch_array($ret)) {
            $memberShips[$row['number']] = array(
                'department' => $row['number'],
                'textline' => $row['textline'],
                'expiry_date' => $row['expiry_date']
            );
        }
        return $memberShips;
    }

    /**
     * internal methode checks if a medium by a user is renewable
     * one methode to rule them all
     *
     * @param array $data raw data from PICA media
     * @param array $borrower user
     * @return array with deparments for current user
     * @access private
     *
     * 2013-04-30, -mp- speedup durch return wenn user=blocked
     * 2013-04-39, -mp- regulations 50 (Wirkung Ausleihindikator)
     */


    private function _picaCheckRenewable($data, $borrower)
    {
        $status = true;
        // override
        if ($this->configArray['Driver']['RenewalsOverride'] == '1') {
            return true;
        }

        if ($this->configArray['Driver']['RenewalsOverride'] == '2') {
            return false;
        }


        // Nutzer blockiert wenn status>2 (hard coded)
        // gleich zurueck
        if ($this->_picaUserStateBlocked() && $this->configArray['Driver']['RenewalsOverride'] != 3) {
            $this->_driverDebugOut('Nutzerstatus >2');
            return false;
        }

        if ($this->configArray['Driver']['RenewalsOverride'] == '4') {
            return true;
        }

        // Medium  ist vorgemerkt
        // gleich zurueck
        if ($data['no_reservations'] != 0) {
            $this->_driverDebugOut('Medium vorgemerkt');
            return false;
        }

        // Mitgliedschfaft für Abteilung noch aktuell ?
        if (!$this->_picaValidMembership($data)) {
            $this->_driverDebugOut('Mitgliedschaft abgelaufen');
            return false;
        }
        // Fernleihe Hack $status&=($data['major_lnic']!=4);
        // Ausleihindikator -> Status >1 nur manuell an der Theke verlaengerbar
        if (!$this->_picaRegulations50($data)) {
            return false;
        }

        $status &= $this->_picaIsExpired($data['expiry_date_loan']);
        //echo 'overdue:'.$status."<br>";
        $status &= $this->_picaRegulations29($data['department_group_nr'], $data['no_renewals']);
        //echo 'Regulations29:'.$status."<br><br>";
        return $status;
    }

    /**
     * internal method for checking loan_indication from  loans_requests_rm_003
     *
     * @param array $data
     * @return bool
     * @access private
     */


    private function _picaRegulations50($data)
    {
        $sql_query = "select subkey1,number1 from regulations where iln=" . $this->picaIln . " and parameter_id=50 and " . $data['major_lnic'] . "=subkey1";
        $ret = mssql_query(sprintf($sql_query, $this->picaFno, PICA_TEXTLINES_DEPARTMENT, $this->picaAddressIdNr), $this->_getLink());
        $row = mssql_fetch_array($ret);
        return ($row['number1'] == 1) ? true : false;
    }

    /**
     * internal methode scrape some data from OPAC (Tag, subtag)
     * presumably only used on Lux and Luy data set to get title and signature
     *
     * @param string ppn, HEB prefixed
     * @return array with signature and title
     * @access private
     */


    private function _picaScrapeByPPN($ppn)
    {
        // HEB strippen
        $ppn = substr($ppn, 3);

        // Array mit Tag und Subtags, die wir wollen
        $grep = array(
            "021A" => array("a" => "title", "h" => "editor"),
            "003@" => array("0" => "ppn"),
        );

        // HTTP-Header vorbereiten
        $file = "http://lbstest.rz.uni-frankfurt.de/CHARSET=ISO8859/XML=1.0/PRS=PP%7F/PPN?PPN=" . $ppn;
        $out = "GET $file HTTP/1.1\r\n";
        $out .= "Host: " . $this->catalogHost . "\r\n";
        $out .= "User-Agent: " . $_SERVER["HTTP_USER_AGENT"] . "\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";
        if (!$conex = @fsockopen($this->catalogHost, "80", $errno, $errstr, 10)) {
            echo $errno, $errstr;
            exit;
        }
        fwrite($conex, $out);
        $data = '';
        $i = 0;
        // in Array
        while (!feof($conex)) {
            $data[$i++] = fgets($conex, 512);
        }
        fclose($conex);
        $out = "";
        for ($i = 0; $i < count($data); $i++) {
            foreach ($grep as $key => $val) {
                if (preg_match("/" . $key . "/", $data[$i])) {
                    $yy = preg_split("/(\\$[a-z0-9])/", $data[$i], -1, PREG_SPLIT_DELIM_CAPTURE);
                    for ($j = 1; $j < count($yy); $j += 2) {
//      2010-02-18, -mp- aeltere Browser haben Probleme mit Unicode aus PICA, deshalb mit ISO8859-1                       
//                         $out[$val[$yy[$j]{1}]]=html_entity_decode(str_replace("@","",$yy[$j+1]));
                        $out[$val[$yy[$j]{1}]] = iconv("ISO8859-1", "UTF-8", html_entity_decode(str_replace("@", "", $yy[$j + 1])));
                    }
                }
            }
        }

        return $out;
    }


    /**
     * Convert date fields from Sybase (should be DB driven)
     *
     * reads data from borrower to class private fields, uses SP of pica
     * @param string $syb_date
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
     *
     * @access private
     */

    private function _sybaseDateToLocalDate($syb_date)
    {
        $timeStamp = strtotime($syb_date);
        //$date=date_parse($syb_date);
        //return $date['day'].".".$date['month'].".".$date['year'];
        if ($this->language == 'DU')
            $format = "d.m.Y";
        else
            $format = "Y-m-d";
        return date($format, $timeStamp);
    }


    /**
     * fill up some PICA user data
     *
     * reads data from borrower to class private fields, uses SP of pica
     * @param string $borrower_bar
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
     *
     * @access private
     */

    private function _picaGetBorrowerData($borrower_bar)
    {
        // SP ous_borrower_select_009 is at least "select * from borrower where borrower_bar='<user>'"
        //$sql_query="ous_borrower_select_009 '".$borrower_bar."'";
        $sql_query = "ous_borrower_select_002 '" . $borrower_bar . "'," . $this->picaIln;
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . __METHOD__);
        }
        // can be only one row
        $row = mssql_fetch_array($ret);
        if (!$row) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . __METHOD__ . mssql_get_last_message());
        } else {
            // retrieve the address_id_nr an ILN used in most other SPs
            $this->picaBorrowerBar = $borrower_bar;
            $this->picaAddressIdNr = $row['address_id_nr'];
            //$this->picaIln=$row['iln'];
            // 0 means allowed, else locked ?
            $this->picaBorrowerStatus = $row['borrower_status'];
            $this->picaGender = $row['gender'];
            $this->picaBorrowerType = $row['borrower_type'];
            $this->picaLanguageCode = $row['language_code'];
            // for retrieving the correct address in profile
            $this->picaReminderAddress = $row['reminder_address'];
            $this->picaEmailAddress = $row['email_address'];
            $this->picaPersonTitles = $row['person_titles'];
            // Bug in der SP: computed1 entspricht free_text
            $this->picaFreeText = $row['computed1'];
            // Bug in der SP: computed2 entspricht message
            $this->picaMessage = $row['computed2'];
            $this->picaFreeTextBlock = $row['free_text_block'];
        }
        // this is fno - outch
        $sql_query = "select convert(tinyint,substring(content1,1,2)) from param where paramcode='OUS' and vlgnr=1 and soort=" . $this->picaIln;
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . __METHOD__);
        }
        // can be only one row
        $row = mssql_fetch_array($ret);
        if (!$row) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . __METHOD__ . mssql_get_last_message());
        } else {
            $this->picaFno = $row[0];
        }
    }

    /**
     * Fetches text from textlines table
     *
     * @param patron array
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
     *
     * @access private
     *
     */


    private function _picaGetTextString($type, $number)
    {
        // Oliver: Abfrage des Sprachcodes entfernt wird ueber Template translate_text uebersetzt
        //$sql_query=sprintf("select textline from textlines where typ=%s and number=%s and language='%s'",$type,$number,$this->language);       
        $sql_query = sprintf("select textline from textlines where typ=%s and number=%s", $type, $number);
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            throw new ILSException('DATABASE: ERROR, ' . mssql_get_last_message());
        }
        $row = mssql_fetch_array($ret);
        //needs error check
        return $row[0];
    }

    private function _queryDB($sql_query)
    {
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . __METHOD__);
        }
        return $ret;
    }

    private function _nextResultDB($ret)
    {
        $row = mssql_fetch_array($ret);
        if (!$row) {
            echo __METHOD__ . ":" . __LINE__;
            echo mssql_get_last_message();
            throw new ILSException('DATABASE: ERROR, ' . __METHOD__ . mssql_get_last_message());
        }
        return ($row);
    }

    /**
     * Gibt die Aktuelle Datenbankverbindung
     * weiter, oder baut eine neue auf, falls keine aktuelle Verbindung steht
     *
     * @return resource - Link zur DB connection
     */
    private function _getLink()
    {

        // falls es eine Verbindung gibt, dann gib die zurück
        if ($this->link !== null) {
            return $this->link;
        } // anderfalls
        else {
            // versuche eine Verbindung aufzubauen
            $link = mssql_connect(
                $this->config['DB-Auth']['SybaseHost'],
                $this->config['DB-Auth']['SybaseUser'],
                $this->config['DB-Auth']['SybasePass']);
            // falls das schief läuft Fehlermeldung
            if ($link == false) {
                // sonst keine gute Fehlermeldung
                echo __METHOD__ . ":" . __LINE__;
                echo mssql_get_last_message() . "\n";
                return null;
            } // sonst setze die Eigenschaft und gibt die Verbindung zurück
            else {
                $this->link = $link;
                return $this->link;
            }
        }
    }


    /* PUBLIC METHODES
    *
    *
    */

    /**
     * Constructor
     *
     * @access public
     */
    public function init()
    {
        parent::init();

        //whats this
        $lang['de'] = 'DU';
        $lang['en'] = 'EN';

        /*
        readConfig()?
                    // 2014-05-21, -mp- wegen ILN
                    $this->mainConfigArray=readConfig();
                    // ticket #276
                    $this->picaIln=$this->mainConfigArray['HeBIS']['ILN'];
                    //printf( "------------ ILN: %s",$this->picaIln);
        */
        $this->picaIln = $this->config['HeBIS']['ILN'];
        $this->catalogHost = $this->config['Catalog']['Host'];


        //TODO: obsolete! Delete the following lines, as soon as your sure it is not necessary
        //$this->renewalsScript = $this->config['Catalog']['renewalsScript'];


        //
        // 2014-09-02, bs, OPAC DB name for renewals and cancellation of holds
        /*
        if ($this->config['Catalog']['OpacDB'])
            $this->picaDBName = $this->config['Catalog']['OpacDB'];
        else
            $this->picaDBName = 1;
        */
        // 2012-05-01, -mp- haesslich, aber fuer Prototyp OK
        // sollte auf PDO umgesetzt und konfigurierbar sein
        //mssql_min_message_severity(15);
        //mssql_min_error_severity(1);

        //  20121024 -mp- hack for language
        //       echo "--------------->".$_REQUEST['mylang']."<-->".$_COOKIE['language']."<-----------";
        if (isset($_REQUEST['mylang']))
            $this->language = $lang[$_REQUEST['mylang']];
        else if (isset($_COOKIE['language']))
            $this->language = $lang[$_COOKIE['language']];
        else
            $this->language = 'DU';

        //          echo "|".$this->language."|";
        // Definitonen fuer Theken aus Datei oder PICA
        if (array_key_exists('Counter', $this->config))
            $this->localCounter = $this->config['Counter'];


    }



    /**
     * Patron Login
     *
     * This is responsible for authenticating a patron against the catalog.
     *
     * @param string $barcode The patron barcode
     * @param string $password The patron password
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
     *
     * @return mixed           Associative array of patron info on successful login,
     * null on unsuccessful login, ILSException on error.
     * @access public
     */
    /**
     * Patron Login
     *
     * This is responsible for authenticating a patron against the catalog.
     *
     * @param string $barcode The patron username
     * @param string $password The patron's password
     *
     * @throws ILSException
     * @return mixed          Associative array of patron info on successful login,
     * null on unsuccessful login.
     */

    public function patronLogin($barcode, $password)
    {
        //Credentials provided by Shibboleth
        $username = $_SERVER['cat_username'];
        $password = $_SERVER['cat_password'];

        //TODO: User object and stuff (return value)

        $sql_query = "ous_borrower_select_002 '" . $username . "'," . $this->picaIln;
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            echo(mssql_get_last_message());
            throw new ILSException("Connection to Sybase DB failed.");
        }
        $row = mssql_fetch_array($ret);

        if (!$row) {
            echo __METHOD__ . ":" . __LINE__;
            echo(mssql_get_last_message());
        } else {
            $userArray = array(
                'id' => $row['borrower_bar'],
                'username' => $row['borrower_bar'],
                'firstname' => PICACharsetMapping::toUTF8($row['first_name_initials_prefix']),
                'lastname' => PICACharsetMapping::toUTF8($row['name']),
            );
            return $userArray;
        }

        return [
            'cat_username' => $_SERVER['cat_username'],
            'cat_password' => $_SERVER['cat_password'],
            'id' => $_SERVER['persistent-id'],
            /*'firstname' =>  $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'username' => $barcode,
            'password' => $password,*/
        ];
    }

    /**
     * Get Patron Profile
     *
     * This is responsible for retrieving the profile for a specific patron.
     *
     * @param array $user The patron array
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
     *
     * @return mixed      Array of the patron's profile data
     *
     * @access public
     */
    public function getMyProfile($user)
    {
        global $configArray;

        $this->_picaGetBorrowerData($user['id']);
        //      $sql_query=sprintf("ous_address_select_001 %s,%s,%s",$this->picaAddressIdNr,$this->picaReminderAddress,$this->picaIln);
        // Aenderung: alle Adressen holen, wegen FL
        $sql_query = sprintf("ous_address_select_002 %s,%s", $this->picaAddressIdNr, $this->picaIln);
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . __METHOD__);
        }
        while ($row = mssql_fetch_array($ret)) {
            if (!$row) {
                echo __METHOD__ . ":" . __LINE__;
                throw new ILSException('DATABASE: ERROR, ' . __METHOD__ . mssql_get_last_message());
            }
            $addresses[] = $row;
        }

        $recordList = array('firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'group' => $this->_picaGetTextString(103, $this->picaBorrowerType),
            'borrower_bar' => $this->picaBorrowerBar,
            'email' => $this->picaEmailAddress,
            // config from _picaGetBorrowerData !!
            'persontitle' => $this->picaPersonTitles,
            'freetext' => $this->picaFreeText,
            'freetextblock' => $this->picaFreeTextBlock,
            'message' => $this->picaMessage,
            'addresses' => $addresses
        );

        foreach ($addresses as $key => $value) {
            if ($addresses[$key][address_code] == $this->picaReminderAddress) {
                $recordList[address1] = PICACharsetMapping::toUTF8($addresses[$key]['address_pob']);
                $recordList[address2] = PICACharsetMapping::toUTF8($addresses[$key]['for_the_attention_of']);
                $recordList[zip] = PICACharsetMapping::toUTF8($row['country'] . " " . $addresses[$key]['postal_code'] . " " . $addresses[$key]['town']);
            }

        }

        $memberships = $this->_picaMemberShips();
        foreach ($memberships as $key => $value)
            $string .= $value['textline'] . " <b>bis:</b> " . $this->_sybaseDateToLocalDate($value['expiry_date']) . "<br>";
        $recordList['memberships'] = $string;
        foreach ($memberships as $key => $value) {
            $recordList['memberships1'][] = array(
                'department' => $value['textline'],
                'expires' => $this->_sybaseDateToLocalDate($value['expiry_date'])
            );

        }

        if (!empty($configArray['System']['debug'])) {
            $this->_driverDebugOut($recordList);
        }
        return $recordList;

    }

    /**
     * Get Patron Transactions
     *
     * This is responsible for retrieving all transactions (i.e. checked out items)
     * by a specific patron.
     * 2012-05-23, -mp- SQL muss noch ueberarbeitet werden, einige Felder landen in "message",
     *                  [ comments removed due to readebility]
     * 2012-10-10, -mp- Aenderung zur Nutzung von SPs
     *                  loans_requests_rm_003 holt alle (und mehr) relevanten Daten fuer Nutzer (address_id_nr,ILN,language_code:du|nl|fr|en)
     * @param array $patron The patron array from patronLogin
     *
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>, Sven Stefani <stefani@bibliothek.uni-kassel.de>
     * @return mixed        Array of the patron's transactions on success, ILSException otherwise.
     * @access public
     */

    public function getMyTransactions($patron)
    {
        // echo "getMyTransactions";
        $this->_picaGetBorrowerData($patron['username']); // fills up some private data of the User: address_id_nr,iln, etc.
        // fetch data for this address
        $sql_query = "loans_requests_rm_003 " . $this->picaAddressIdNr . "," . $this->picaIln . "," . $this->picaLanguageCode;

        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . mssql_get_last_message());
        }
        while ($row = mssql_fetch_array($ret)) {
            // awful temp. hack to keep SP
            if ($row['loan_status'] == 5) {
                // 2013-01-13, -mp- sollte nicht mehr vorkommen, liess sich am LBS KS + FFM nicht mehr
                // reproduzieren
                if (!$row['ppn']) {
                    $level2 = $this->_picaReadTitlesCopy($row['epn']);
                    $row['shorttitle'] = $row['signature'] = $level2['209A'][0]['a'][0];
                    $row['standort'] = $level2['209A'][0]['z'][0];
                    $row['ppn'] = $level2['ppn'];
                }

                $transactions[] = array(
                    'id' => $this->_picaPPNPZ($row['ppn']),  // PPN
                    'duedate' => $this->_sybaseDateToLocalDate($row['expiry_date_loan']),
                    'volume' => $row['volume_bar'],              // Buchnr.
                    'barcode' => substr($row['signature'], 3),              // Signatur
                    'abtcode' => "abt" . substr($row['signature'], 0, 3),
                    'rawsignature' => $row['signature'],
                    'standort' => (isset($row['standort']) ? 'abt' . $row['standort'] : null),
                    'title' => utf8_encode($row['shorttitle']),    // Shorttitle aus ous_copy_cache
                    'renewable' => $this->_picaCheckRenewable($row, $this->picaBorrowerBar),
                    'borrower_bar' => $this->picaBorrowerBar,
                    'renew' => $row['no_renewals'], // done renewels
                    'request' => $row['no_reservations'],
                    'item_id' => $row['volume_bar'],
                    'pica_extra_information' => $row['extra_information']
                );
            }
        }
        // oldest first, like OPAC
        $this->_driverDebugOut(array_reverse($transactions));
        return array_reverse($transactions);
    }


    /**
     * Get Patron Fines
     *
     * This is responsible for retrieving all fines by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return mixed        Array of the patron's fines on success, ILSException
     * otherwise.
     * @access public
     */
    public function getMyFines($patron)
    {
        $this->_picaGetBorrowerData($patron['username']);
        $departments = $this->_picaMemberShips();
        // $sql_query="select ous_copy_cache.ppn,requisition.* from requisition,volume,ous_copy_cache where (address_id_nr=".$this->picaAddressIdNr." and volume.volume_number=requisition.id_number) and (volume.epn=ous_copy_cache.epn) order by department_group_nr asc";
        //$sql_query="select titles_copy.ppn,requisition.* from requisition,volume,titles_copy where (address_id_nr=".$this->picaAddressIdNr." and volume.volume_number=requisition.id_number) and (volume.epn=titles_copy.epn) and fno=".$this->picaFno." order by department_group_nr asc";
        // 2013-05-16 -mp- aus einer SP (requisition_rm_003) extarhiert.
        $sql_query = '
declare @iln integer,@code_type integer,@address_id_nr integer,@language varchar(2),@department_group_nr integer
select @iln=' . $this->picaIln . ',@code_type=3,@address_id_nr=' . $this->picaAddressIdNr . ',@language="DU",@department_group_nr=1

 
    SELECT  a.address_id_nr,
            a.costs_code,
            a.id_number,
            a.costs,
            a.date_of_creation,
            a.date_of_discharge,
            a.department_group_nr,
            a.edit_date,
            a.extra_information,
            a.iln,
            a.pica_timestamp,            
            (select description from lbs_code_desc where iln = @iln and code_id = @code_type and code_value = convert(char(5), a.costs_code) and language_code = @language) as description,
            a.date_of_issue, 
            a.date_of_expiration,
            v.volume_bar,
            isNull(c.shorttitle, "************"),
            isNull(c.author, "************"),
            c.signature,
            v.shelf_mark_deviant,
            (select count(*) from loans_requests where loans_requests.volume_number = a.id_number and loans_requests.iln = a.iln and loans_requests.address_id_nr = a.address_id_nr) as nr_loans,
            "" as selected,
            "" as amount_collected,
            "" as checkable,
            c.ppn,
            v.epn
    FROM    requisition a, ous_copy_cache c, volume v
    WHERE   a.address_id_nr = @address_id_nr
    AND     a.iln = @iln 
    AND     a.id_number *= v.volume_number
    AND     a.iln *= v.iln
    AND     c.epn =* v.epn
    AND     c.iln =* v.iln
    AND     c.fno =* (select convert(tinyint, substring(content1, 1, 2)) from param where paramcode = "OUS" and vlgnr = 1 and soort = v.iln)
    AND     a.costs_code in (1, 2, 3, 4, 8)
UNION
    SELECT  a.address_id_nr,
            a.costs_code,
            a.id_number,
            a.costs,
            a.date_of_creation,
            a.date_of_discharge,
            a.department_group_nr,
            a.edit_date,
            a.extra_information,
            a.iln,
            a.pica_timestamp,            
            (select description from lbs_code_desc where iln = @iln and code_id = @code_type and code_value = convert(char(5), a.costs_code) and language_code = @language) as description,
            a.date_of_issue, 
            a.date_of_expiration,
            "" as volume_bar,
            "" as shorttitle,
            "" as author,
            "" as signature,
            "" as shelf_mark_deviant,
            (select count(*) from loans_requests where loans_requests.volume_number = a.id_number and loans_requests.iln = a.iln and loans_requests.address_id_nr = a.address_id_nr) as nr_loans,
            "" as selected,
            "" as amount_collected,
            "" as checkable,
            0 as blabla,
            0 as gaga
    FROM    requisition a
    WHERE   a.address_id_nr = @address_id_nr
    AND     a.iln = @iln 
    AND     (a.costs_code in (5, 6, 7, 9) OR a.costs_code >= 10)
       
       ';

        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException('DATABASE: ERROR, ' . mssql_get_last_message());
        }
        $sum = 0.0;
        while ($row = mssql_fetch_assoc($ret)) {

            $fineList[] = array(
                "amount" => $row['costs'] * 100,
                "balance" => $row['costs'] * 100,
                "checkout" => "",
                "Costs_code" => $row['costs_code'],
                "PICA_TEXTLINES_FINES" => PICA_TEXTLINES_FINES,
                "fine" => PICACharsetMapping::toUTF8($this->_picaGetTextString(PICA_TEXTLINES_FINES, $row['costs_code'])),
                "duedate" => $this->_sybaseDateToLocalDate($row['date_of_creation']),
                "id" => $this->_picaPPNPZ($row['ppn']),
                "department" => $departments[$row['department_group_nr']]['textline'],
                "departmentnames" => $departments,
                "is_volume" => (($row['costs_code'] > 0) && ($row['costs_code'] <= 4))
            );
            $sum += $row['costs'] * 100.0;


        }
        $this->_driverDebugOut($fineList);

        return $fineList;

    }


    /**
     * Define counter_id and counter description
     *
     * @param patron array
     * @author   Michael Plate <plate@bibliothek.uni-kassel.de>
     *
     * @access public
     *
     */

    public function getPickUpLocations($patron)
    {
        // in *ini defined Counter names ?
        if (isset($this->localCounter)) {
            foreach ($this->localCounter as $key => $value) {
                $locations[] = array(
                    'locationID' => $key,
                    'locationDisplay' => $value
                );
            }
            return $locations;
        }
        // else
        $sql_query = sprintf("select * from counter where iln=%s", $this->picaIln);
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            throw new ILSException('DATABASE: ERROR, ' . mssql_get_last_message());
        }
        while ($row = mssql_fetch_array($ret)) {
            $locations[] = array(
                'locationID' => $row['counter_number'],
                'locationDisplay' => $row['description']
            );
        }
        return $locations;
    }


    /**
     * Get Patron Holds
     *
     * This is responsible for retrieving all holds by a specific patron.
     * 2012-10-15, -mp- fno aus Query (keine SP moeglich)
     * 2012-11-01, -mp- shorttitle hinzu fuer Vormerkung
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return mixed        Array of the patron's holds on success, ILSException
     * otherwise.
     * @access public
     */
    public function getMyHolds($patron)
    {
        //echo "getMyHolds";
        /* Oliver -> Abfrage der period_of_loan in Frankfurt über andere Datenbanktabelle und Signature für type = 5 ergänzt
         *           Warteschlangeaufgetrennt, da Buch von Typ 4 auch nicht ausleihbar wenn Warteschlange > 0 */

        //$sql_query="select titles_copy.ppn,convert(char(20),reservation.reservation_date_time,104) as reservation_date_time,reservation.period_of_loan, volume.*,borrower.iln from ous_copy_cache,titles_copy,reservation,volume, borrower where reservation.address_id_nr=borrower.address_id_nr and borrower.borrower_bar='%s' and volume.volume_number=reservation.volume_number and titles_copy.epn=volume.epn and titles_copy.fno=%s and titles_copy.epn=ous_copy_cache.epn";
        //$sql_query="select titles_copy.ppn,convert(char(20),reservation.reservation_date_time,104) as reservation_date_time,reservation.period_of_loan as period_of_loan_ks,loans_requests.period_of_loan as period_of_loan_ffm,convert(char(20),loans_requests.expiry_date_loan,104) as expiry_date_loan,convert(char(20),loans_requests.expiry_date_reminder,104) as expiry_date_reminder,loans_requests.no_reminders, volume.*,borrower.iln, ous_copy_cache.signature from ous_copy_cache,titles_copy,reservation,volume,loans_requests,borrower where reservation.address_id_nr=borrower.address_id_nr and borrower.borrower_bar='%s' and volume.volume_number=reservation.volume_number and loans_requests.volume_number=volume.volume_number and titles_copy.epn=volume.epn and titles_copy.fno=%s and titles_copy.epn=ous_copy_cache.epn";
        $sql_query = "select titles_copy.*,convert(char(20),reservation.reservation_date_time,104) as reservation_date_time,loans_requests.period_of_loan ,convert(char(20),loans_requests.expiry_date_loan,104) as expiry_date_loan,convert(char(20),loans_requests.expiry_date_reminder,104) as expiry_date_reminder,loans_requests.no_reminders, volume.*,borrower.iln from titles_copy,reservation,volume,loans_requests,borrower where reservation.address_id_nr=borrower.address_id_nr and borrower.borrower_bar='%s' and volume.volume_number=reservation.volume_number and loans_requests.volume_number=volume.volume_number and titles_copy.epn=volume.epn and titles_copy.fno=%s";

        $this->_picaGetBorrowerData($patron['username']);
        $ret = mssql_query(sprintf($sql_query, $patron['username'], $this->picaFno), $this->_getLink());
        $i = 0;
        while ($row = mssql_fetch_array($ret)) {


            if (!$row['signature']) {
                $level2 = $this->_picaReadTitlesCopy($row['epn']);
                //echo "<pre>";print_r($level2);echo "</pre>pre>";
                $row['shorttitle'] = $row['signature'] = $level2['209A'][0]['a'][0];


            }

            $holdList[trim($row['reservation_date_time']) . $i] = array(
                'id' => $this->_picaPPNPZ($row['ppn']),  // PPN
                'create' => $row['reservation_date_time'],
                //'expire_res'  => ($row['period_of_loan_ks']>0)?date('d.m.Y',(3600*24*$row['period_of_loan_ks'])+(int)date_timestamp_get(date_create_from_format('d.m.Y',trim($row['reservation_date_time'])))):'-',
                'expire_loan' => ($row['period_of_loan'] > 0) ? date('d.m.Y', (3600 * 24 * $row['period_of_loan']) + (int)date_timestamp_get(date_create_from_format('d.m.Y', trim($row['reservation_date_time'])))) : '-',
                'expiry_date' => $row['expiry_date_reminder'],
                'expiry_date_loan' => $row['expiry_date_loan'],
                'no_reminders' => $row['no_reminders'],
                'volume' => $row['volume_bar'],
                'position_queuestatus' => PICACharsetMapping::toUTF8($this->_picaGetTextString(PICA_TEXTLINES_LOAN, $row['loan_status'])),
                'position_queuenumber' => $this->_picaGetReservationPosition($row['volume_number']),
                'title' => $row['shorttitle'],
                'type' => $row['loan_status'],
                'signature' => $row['signature'],

            );
            $i++;
        }

        $sql_query = "loans_requests_rm_003 " . $this->picaAddressIdNr . "," . $this->picaIln . "," . $this->picaLanguageCode;
        $ret = mssql_query($sql_query, $this->_getLink());
        while ($row = mssql_fetch_array($ret)) {
            if (($row['loan_status'] != '5')) {
                $holdList[$this->_sybaseDateToLocalDate($row['date_time_of_loans_request']) . $i] = array(
                    'id' => $this->_picaPPNPZ($row['ppn']),
                    'volume' => $row['volume_bar'],
//                               'location' => $row['destination_counter_desc'],
                    'location' => $row['counter_nr_destination'],
                    'create' => $this->_sybaseDateToLocalDate($row['date_time_of_loans_request']),
                    'title' => $row['shorttitle'],
                    'position_queuestatus' => PICACharsetMapping::toUTF8($this->_picaGetTextString(PICA_TEXTLINES_LOAN, $row['loan_status'])),
                    'position_queuenumber' => $this->_picaGetReservationPosition($row['volume_number']),
                    'type' => $row['loan_status'],
                    'signature' => substr($row['signature'], 3),
                    'signature_full' => $row['signature']
                );
            }
            $i++;

        }

        asort($holdList);
        $this->_driverDebugOut($holdList);
        return $holdList;
    }

    /**
     * Renew Items
     * This method renews every selected media _single_
     * This makes ist easy to asort the right error to the media
     *
     * @param array $renewdetails Details for renewing filled up by getRenewDetails
     *
     * @return mixed        Array of infos on the medium
     * @access public
     */
    public function renewMyItems($renewdetails)
    {
        $renewItems = array('count' => '0', 'items' => array());
        // uuuuuuuugggggggglllllllllyyyyyyyyyyyy
        $this->picaBorrowerBar = $renewdetails['patron']['id'];

        if ((!isset($_REQUEST['password']) | ($_REQUEST['password']) == "")) {
            return false;
        }

        foreach ($renewdetails['details'] as $key => $value) {
            $renew = $this->_renew($value, $_REQUEST['password']);
            if ($renew != null) {
                $renewItems['count']++;
            } else {
                $renewItems['count'] = "-1";
            }
            $renewItems['items'][$key] = $renew;
        }
        return $renewItems;
    }

    /**
     * ID for renewing - here: volume field
     *
     *
     * @param array $details Details for renewing filled up by getMyTransactions
     *
     * @return mixed        Array of infos on the medium
     * @access public
     */

    public function getRenewDetails($details)
    {
        return $details['volume'];
    }


    /**
     * cancel a hold on media
     *
     * @param array $cancelDetails volume_bars
     * @param string $renewals current renewals on media
     * @return array with cancelDetails, not fully working on PICA
     * @access private
     */

    public function cancelHolds($cancelDetails)
    {
        $cancelHolds = array('count' => '0', 'items' => array());
        if ((!isset($_REQUEST['password']) | ($_REQUEST['password']) == "")) {
            return false;
        }
        $this->picaBorrowerBar = $cancelDetails['patron']['username'];
        $URL = "/loan/DB=" . $this->picaDBName . "/LNG=DU/USERINFO";
        $POST = array(
            "ACT" => "UI_CANCELRES",
            "BOR_U" => $this->picaBorrowerBar,
            "BOR_PW" => $_REQUEST['password']
        );

        foreach ($cancelDetails['details'] as $key) {
            $POST['VB'] = $key;
            $postit = $this->_postit($URL, $POST);
            if ($postit != null) {
                $cancelHolds['count']++;
            } else {
                $cancelHolds['count'] = "-1";
            }
            $cancelHolds['items'][$key] = array(
                'success' => false,
                'status' => 'hold_cancel_fail',
                'sysMessage' => $postit
            );
        }


        return $cancelHolds;
    }

    /**
     * get details on media for canceling
     *
     * @param array $holdDetails media data, mostly complete record from PICA
     * @return string volumenumber on success   true if lower than lower limit (OPAC)
     * @access public
     */

    public function getCancelHoldDetails($holdDetails)
    {
        /* Oliver -> Abfrage auf Warteschlange ergänzt da auch Typ 4 cancelbar sein kann */
        if (($holdDetails['type'] == 0) || ($holdDetails['position_queuenumber'] > 0))
            return $holdDetails['volume'];
        else
            return false;
    }

    /**
     * read metadata directly from titles_copy
     *
     * @param epn
     * @return array with level2-data
     * @access private
     */

    private function _picaReadTitlesCopy($epn)
    {
        $sql_query = 'select * from titles_copy where fno=' . $this->picaFno . ' and epn=' . $epn;
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException(mssql_get_last_message());
        }
        $row = mssql_fetch_array($ret);
        $ppn = $row['ppn'];
        // 2014-01-10, -mp- Bug in PICA
        if ($row['length'] > 254)
            for ($i = 1; $i < 9; $i++) {
                if (strlen($row['mark' . $i]) == 254) {
                    $row['mark' . $i] .= " ";
                }
            }
        $mark = $row['mark1'] . $row['mark2'] . $row['mark3'] . $row['mark4'] . $row['mark5'] . $row['mark6'] . $row['mark7'] . $row['mark8'];
        // 2014-0306, -mp- length aus DB nehmen
        $len = $row['length'];
        // 2014-01-10, -mp- Rest aus titles_overflow , YEEEHAAAAA
        if ($row['length'] > strlen($mark)) {
            $sql_query = 'select * from title_overflow  where fno=' . $this->picaFno . ' and epn=' . $epn;
            $ret = mssql_query($sql_query, $link);
            if ($ret === false) {
                echo __METHOD__ . ":" . __LINE__;
                echo mssql_get_last_message();
            }
            $row = mssql_fetch_array($ret);
            // 2014-01-10, -mp- Bug in PICA
            if ($row['length'] > 254)
                for ($i = 1; $i < 9; $i++) {
                    if (strlen($row['mark' . $i]) == 254) {
                        $row['mark' . $i] .= " ";
                    }
                }
            $mark .= $row['mark1'] . $row['mark2'] . $row['mark3'] . $row['mark4'] . $row['mark5'] . $row['mark6'] . $row['mark7'] . $row['mark8'];
        }
        // 2014-04-06, -mp- len uebergeben
        $level2 = $this->_picaSplitset($mark, $len);
        $level2['ppn'] = $ppn;
        //echo "<pre>";
        //print_r($level2);exit;
        return $level2;
    }

    /**
     * split tag records from titles_*,title_overflow
     *
     * 2013-12-12, -mp-
     *
     * @string String with whole records from mark*
     * @pointer Pointer to start with in the record
     * @len Length of the String
     * @return array with subtags
     * @access private
     */

    private function _picaSplitSub($string, $pointer, $len)
    {
        $j = $pointer;
        while (($j + $j % 2) < $len + $pointer) {
            $pica_subtag = $string{$j++};
            $sub_len = ord($string{$j++});
            // take it all as one sub tag
            if ($sub_len == 255)
                $sub_len = $len;
            $pica_text = substr($string, $j, $sub_len - 2);
            $j += $sub_len - 2;
            $pica_text = PICACharsetMapping::toUTF8($pica_text);
            $subtags[$pica_subtag] = array($pica_text);
        }
        //echo "<pre>";print_r($subtags);echo "</pre>";
        return $subtags;
    }

    /**
     * split subtags from tag record
     *
     * @param string binary data
     * @param len length of binary data
     * @return array with sub tags
     * @access private
     */

    private function _picaSplitset($string, $len)
    {
        $j = 0;
        // 2014-03-06, -mp- falsch, jetzt aus Parameter $len
        //$len=strlen($string);
        $pointer = 0;

        //  bugfix bs, $len-1
        // 2014-03-06, -mp- erledigt mit Uebergabe $len
        while ($pointer < $len) {
            $tag = ord($string[$pointer++]) + 256 * ord($string[$pointer++]);
            $pica_len = (int)(ord($string[$pointer++]) + 256 * ord($string[$pointer++]));
            $pica_len = $pica_len + $pica_len % 2;
            $pica_code = sprintf('%03d', (ord($string[$pointer])) & 128 ? $tag + 200 : $tag) . chr(64 + ord($string[$pointer++]) & 127);
            $pointer++;
            $pica_text = $this->_picaSplitSub($string, $pointer, $pica_len - 6);
            $pointer += $pica_len - 6;
            //$pica_code=array($pica_text);
            $set[$pica_code][] = $pica_text;
        }
        return $set;
    }

    /**
     * get position in reservation
     *
     * @param  volume_number
     * @return numeric position
     * @access private
     */

    private function _picaGetReservationPosition($volume_number)
    {
        //  echo $volume_number." ".$this->picaIln."<br>";
        $sql_query = 'reservation_rm_009 %s,%s';
        $ret = mssql_query(sprintf($sql_query, $volume_number, $this->picaIln));
        while ($row = mssql_fetch_array($ret)) {
            $reservations[strtotime($row['reservation_date_time'])] = $row;
        }
        // 2013-05-16, -mp- nicht belegt bei Bestellungen
        if (!isset($reservations))
            return null;
        // 2013-11-08 -mp- weglassen, sonst falsche Position
        //		arsort($reservations,SORT_NUMERIC);
        $i = 1;
        foreach ($reservations as $key) {
            if ($key['address_id_nr'] == $this->picaAddressIdNr)
                return $i;
            else
                $i++;
        }

    }

    /**
     * get Membership
     *
     * @param  data
     * @return boolean
     * @access private
     */

    private function _picaValidMembership($data)
    {
        $sql_query = "select expiry_date from membershp where address_id_nr=%s and department_group_nr=%s";
        $ret = mssql_query(sprintf($sql_query, $this->picaAddressIdNr, $data['department_group_nr']));
        $row = mssql_fetch_array($ret);
        if (strtotime($row['expiry_date']) < time()) {
            if (strtotime($row['expiry_date']) == "") {
                return true;
            }
            return false;
        }
        return true;
    }


    /* temp temp temp
    */

    private function _picaGetPPNByEPN($epn)
    {
        $sql_query = 'select ppn from titles_copy where fno=' . $this->picaFno . ' and epn=' . $epn;
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException(mssql_get_last_message());
        }
        $row = mssql_fetch_array($ret);
        return $row['ppn'];

    }


    private function _picaReadTitlesGlobal($ppn)
    {
        $sql_query = 'select * from titles_global where fno=' . $this->picaFno . ' and ppn=' . $ppn;
        $ret = mssql_query($sql_query, $this->_getLink());
        if ($ret === false) {
            echo __METHOD__ . ":" . __LINE__;
            throw new ILSException(mssql_get_last_message());
        }
        $row = mssql_fetch_array($ret);
        $mark = substr($row['mark1'] . $row['mark2'] . $row['mark3'] . $row['mark4'] . $row['mark5'] . $row['mark6'] . $row['mark7'] . $row['mark8'], 0, $row['length']);
        $level0 = $this->_picaSplitset($mark);
        return $level0;
    }


    private function _picaRegulations14($department)
    {
        $sql_query = "select * from regulations where parameter_id=14 and iln=%s and department_group_nr=%s and subkey1=%s";
        $ret = mssql_query(sprintf($sql_query, $this->picaIln, $department, $this->picaBorrowerType), $this->_getLink());
        $row = mssql_fetch_array($ret);


    }

    private function _picaRegulations28($department)
    {
        $sql_query = "select * from regulations where parameter_id=28 and iln=%s and department_group_nr=%s";
        $ret = mssql_query(sprintf($sql_query, $this->picaIln, $department), $this->_getLink());
        $row = mssql_fetch_array($ret);
        return $row['number1'];
    }

    private function _picaRegulations15()
    {
        // oh fuck
    }

    private function _picaCostsCacheInit()
    {
        $this->picaCostsCache = array(
            'revalidate' => time() + 60,
            'departments' => array()
        );
        $sql_query = "select requisition.costs,requisition.department_group_nr from requisition,volume,ous_copy_cache where (address_id_nr=%s and volume.volume_number=requisition.id_number) and (volume.epn=ous_copy_cache.epn) ";
        $ret = mssql_query(sprintf($sql_query, $this->picaAddressIdNr), $this->_getLink());
        while ($row = mssql_fetch_array($ret)) {
            $this->picaCostsCache[$row['department_group_nr']] += $row['costs'] * 100.;
        }
    }

    private function _picaCostsCacheRevalidate()
    {
        if ($this->picaCostsCache['revalidate'] <= time()) {
            unset($this->picaCostsCache);
            $this->_picaCostsCacheInit();
        }
    }

    private function _picaRegulationsCacheQuery($sql_query)
    {
        $hash = md5($sql_query);
        if (!isset($this->picaRegulationsCache[$hash])) {
            $ret = mssql_query($sql_query, $this->_getLink());
            while ($row = mssql_fetch_array($ret)) {
                $this->picaRegulationsCache[$hash][]['iln'] = $row['iln'];
                $this->picaRegulationsCache[$hash][]['department_group_nr'] = $row['department_group_nr'];
                $this->picaRegulationsCache[$hash][]['parameter_id'] = $row['parameter_id'];
                $this->picaRegulationsCache[$hash][]['subkey1'] = $row['subkey1'];
                $this->picaRegulationsCache[$hash][]['subkey2'] = $row['subkey2'];
                $this->picaRegulationsCache[$hash][]['subkey3'] = $row['subkey3'];
                $this->picaRegulationsCache[$hash][]['subkey4'] = $row['subkey4'];
                $this->picaRegulationsCache[$hash][]['number1'] = $row['number1'];
                $this->picaRegulationsCache[$hash][]['number2'] = $row['number2'];
            }
        }
    }


    private function _picaGetNumReservations($volume_number)
    {
        $sql_query = 'select count(*) from reservation,volume where ( reservation.volume_number = volume.volume_number ) and ( reservation.volume_number = %s ) and ( volume.iln = %s) and ( reservation.iln = %s )';
        $ret = mssql_query(sprintf($sql_query, $volume_number, $this->picaIln, $this->picaIln));
        $row = mssql_fetch_array($ret);
        return $row['count'];
    }


    private function _driverDebugOut($var)
    {
        if (DRIVER_DEBUG != "deadbeaf")
            return false;
        if (strpbrk($_SERVER['REQUEST_URI'], "DEBUG")) {
            echo "<div style='border:1px solid red;background:white'><pre>";
            print_r($var);
            echo "</pre></div>";
        }

    }

    protected $services;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->services = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->services;
    }

    public function dispatch(Request $request, Response $response = null)
    {
        // ...

        // Retrieve something from the service manager
        $router = $this->getServiceLocator()->get('Router');

        // ...
    }
}

?>
