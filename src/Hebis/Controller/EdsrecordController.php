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

namespace Hebis\Controller;

use Zend\Http\Request;
use Zend\View\Model\ViewModel;

/**
 * Class EdsrecordController
 * @package Hebis\Controller
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class EdsrecordController extends \VuFind\Controller\EdsrecordController
{
    /**
     * @return ViewModel
     */
    public function redilinkAction()
    {
        $view = $this->createViewModelAjax();

        $params = $this->params()->fromQuery('id');
        $driver = $this->loadRecord();

        $fields = $driver->getFields();
        if (array_key_exists("FullText", $fields) &&
            array_key_exists("CustomLinks", $fields["FullText"])) {
            $first = array_values($fields["FullText"]["CustomLinks"])[0];
            $html = $this->fetchRediLink($first["Url"]);
            $view->infoLink = $this->parseInfoLink($html);
            if (empty($infoLink)) {
                $view->rediLink = $first;
            }
            $view->ezbLinks = $this->parseEzbLinks($html);
        }
        $view->driver = $driver;
        $view->params = $params;
        return $view;
    }

    /**
     * @param $url
     * @return string
     */
    private function fetchRediLink($url)
    {

        /** @var \Zend\Http\Client $client */
        $client = $this->serviceLocator
            ->get('VuFind\Http')
            ->createClient($url, Request::METHOD_GET);

        $request = new Request();
        $request->setUri($url);
        $request->setMethod(Request::METHOD_GET);

        $response = $client->send($request);

        return $response->getBody();

    }

    /**
     * @param $html
     * @return bool|string
     */
    private function parseInfoLink($html)
    {
        $infolink = "";

        if (stripos($html, "<span class=\"t_ezb_green\">") !== false ||
            stripos($html, "<span class=\"t_ezb_yellow\">")!== false) {
            $start = stripos($html, "<span class=\"t_infolink\">");
            $infolink = substr($html, $start+25);
            $stop = stripos($infolink, "i</a>");
            $infolink = substr($infolink, 0, $stop);
            $s = stripos($infolink, "href=\"") + 6;
            $e = stripos($infolink, "\" t");
            $infolink = substr($infolink, $s, $e - $s);
        }
        return $infolink;
    }

    /**
     * @param $html
     * @return array
     */
    private function parseEzbLinks($html)
    {
        $ret = [];
        while (stripos($html, "<span class=\"t_ezb_green\">")!== false ||
            stripos($html, "<span class=\"t_ezb_yellow\">")!== false) {
            // Anfang
            $start = stripos($html, "<span class=\"t_ezb_");
            $rest = substr($html, $start);

            // Ampel
            $start = stripos($rest, "<span class=\"t_ezb_");
            $ampel = substr($rest, $start);
            $stop = stripos($ampel, "</span>");
            $ampel = substr($ampel, 0, $stop+7);
            $ampel = $this->trafficLight($ampel);
            // volltextlink
            $start = stripos($rest, "<span class=\"t_link\">");
            $direktlink = substr($rest, $start+21);
            $stop = stripos($direktlink, "</span>");
            $direktlink = substr($direktlink, 0, $stop);
            $direktlink = str_replace("<sup>*</sup>", "", $direktlink);

            // volltexthinweistext
            $start = stripos($rest, "&nbsp;</span>");
            $vtext = substr($rest, $start+13);
            $stop = stripos($vtext, "<span class=\"t_link\">");
            $vtext = "&#187;".str_replace("<sup>*</sup>", "", substr($vtext, 0, $stop));

            $direktlink = str_replace(">&#187;<", ">".$vtext."<", $direktlink);

            // anhaengen an Ergebnisarray
            $pos['direktlink'] = $direktlink;
            $pos['ampel'] = $ampel;
            $ret[] = $pos;

            $html = substr($rest, 2);
        }

        return $ret;
    }

    /**
     * @param null $params
     * @return ViewModel
     */
    protected function createViewModelAjax($params = null)
    {
        $this->layout()->setTemplate('layout/lightbox');
        return new ViewModel($params);
    }

    /**
     * @param $ampel
     * @return string
     */
    private function trafficLight($ampel)
    {
        if (preg_match("/class=\"t_ezb_(green|yellow|red)\"/", $ampel, $match)) {
            $ret = "<span class=\"ezb-traffic-light\">";
            $green = "<span class=\"badge ezb green\">&nbsp;</span>";
            $yellow = "<span class=\"badge ezb yellow\">&nbsp;</span>";
            $red = "<span class=\"badge ezb red\">&nbsp;</span>";
            switch ($match[1]) {
                case "green":
                    $green = preg_replace("/green/", "green active", $green);
                    break;
                case "yellow":
                    $yellow = preg_replace("/yellow/", "yellow active", $yellow);
                    break;
                case "red":
                    $red = preg_replace("/red/", "red active", $red);
                    break;
            }
            return $ret . $green . $yellow . $red . "</span>";
        }
        return "";
    }
}
