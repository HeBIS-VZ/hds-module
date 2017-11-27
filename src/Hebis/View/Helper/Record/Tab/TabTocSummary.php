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

use DOMDocument;
use Hebis\Marc\Helper;
use Hebis\RecordDriver\SolrMarc;
use Hebis\View\Helper\Record\AbstractRecordViewHelper;
use VuFindCode\ISBN;
use Zend\Config\Config;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Uri\Http as Url;


/**
 * Class TabToc
 * @package Hebis\View\Helper\Record\Tab
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class TabTocSummary extends AbstractRecordViewHelper
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SolrMarc
     */
    private $record;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function __invoke(SolrMarc $record)
    {
        $this->record = $record;
        return $this;
    }

    public function getAbstract()
    {
        /** @var \File_MARC_Record $marcRecord */
        $marcRecord = $this->record->getMarcRecord();

        $ret = [];

        $fields520 = $marcRecord->getFields(520);

        /** @var \File_MARC_Data_Field $field */
        foreach ($fields520 as $field) {
            $a = Helper::getSubField($field, "a");
            $ret[] = str_replace("Abstract-Anfang", "", $a);
        }

        return implode("<br />\n", $ret);
    }

    public function getContentNotes()
    {
        $ret = [];
        $marcRecord = $this->record->getMarcRecord();
        $fields856 = array_filter($marcRecord->getFields(856), function($field) {
            /** @var \File_MARC_Data_Field $field */
            return $field->getIndicator(2) == 2;
        });
        foreach ($fields856 as $field) {
            $u = Helper::getSubField($field, "u");
            $_3 = Helper::getSubField($field, "3");

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

    public function getSyndeticsToc()
    {

        //list of syndetic toc
        $sourceList = array(
            'TOC' => array(
                'title' => 'TOC',
                'file' => 'TOC.XML',
                'div' => '<div id="syn_toc"></div>'
            )
        );

        //first request url
        $config = $this->config->get("Syndetics");

        if (!empty($config['plus'] && !empty($config['plus_id']))) {
            $url = 'http://syndetics.com' . '/index.aspx?isbn=' . $this->getIsbn() .
                '/index.xml&client=' . $config['plus_id'] . '&type=rw12,hw7';
        } else {
            return "";
        }
        //find out if there are any toc
        /** @var Response $response */
        $response = $this->syndeticsRequest($url);
        if (!$response->isOk()) {
            throw new \Exception($response->getReasonPhrase());
        }

        $xmldoc = $this->loadXml($response->getBody());

        $review = array();
        $i = 0;

        foreach ($sourceList as $source => $sourceInfo) {

            $nodes = $xmldoc->getElementsByTagName($source);
            if ($nodes->length > 0) {
                // Load toc
                $url = 'http://syndetics.com' . '/index.aspx?isbn=' . $this->getIsbn() . '/' .
                    $sourceInfo['file'] . '&client=' . $config['plus_id'] . '&type=rw12,hw7';
                $response = $this->syndeticsRequest($url);
                $xmldoc2 = $this->loadXml($response->getBody());

                // If we have syndetics plus, we don't actually want the content
                // we'll just stick in the relevant div
                if (isset($s_plus)) {
                    $review[$i]['Content'] = $sourceInfo['div'];
                } else {
                    // Get the marc field for toc (970)
                    $nodes = $xmldoc2->GetElementsbyTagName("Fld970");

                    if (!$nodes->length) {
                        // Skip toc with missing text
                        continue;
                    }

                    $j=0;
                    foreach ($nodes as $node)
                    {
                        foreach ($node->childNodes as $child) {
                            $review[$i]['Content'][$j][$child->nodeName]= html_entity_decode($child->textContent);
                        }

                        $j++;
                    }

                    // Get the marc field for copyright (997)
                    $nodes = $xmldoc->GetElementsbyTagName("Fld997");
                    if ($nodes->length) {
                        $review[$i]['Copyright'] = html_entity_decode(
                            $xmldoc2->saveXML($nodes->item(0))
                        );
                    } else {
                        $review[$i]['Copyright'] = null;
                    }

                    if ($review[$i]['Copyright']) {  //stop duplicate copyrights
                        $location = strripos(
                            $review[0]['Content'], $review[0]['Copyright']
                        );
                        if ($location > 0) {
                            $review[$i]['Content']
                                = substr($review[0]['Content'], 0, $location);
                        }
                    }
                }

                // change the xml to actual title:
                $review[$i]['Source'] = $sourceInfo['title'];

                $review[$i]['ISBN'] = $this->getIsbn(); //show more link
                $review[$i]['username'] = $config['plus_id'];

                $i++;
            }
        }

        return $review;
    }


    /**
     * @param $xmlString
     * @return mixed
     * @throws \Exception
     */
    private function loadXml($xmlString)
    {
        $dom = @DOMDocument::loadXML($xmlString);
        if (!$dom) {
            throw new \Exception("Invalid XML");
        }
        return $dom;
    }

    /**
     * Attempt to get an ISBN-10; revert to ISBN-13 only when ISBN-10 representation
     * is impossible.
     *
     * @return string
     * @access private
     */
    private function getIsbn()
    {
        $isbn = new ISBN($this->record->getISBNs()[0]);
        $isbn10 = $isbn->get10();

        if (!$isbn10) {
            return $isbn->get13();
        }
        return $isbn10;
    }

    /**
     * @param $url
     * @return Response
     */
    private function syndeticsRequest($url)
    {
        $client = new Client();
        $client->setMethod(Request::METHOD_GET);
        $client->setUri(new Url($url));
        /** @var \Zend\Http\Response $response */
        $response = $client->send();
        return $response;
    }
}
