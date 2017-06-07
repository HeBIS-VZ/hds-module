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

namespace Hebis\View\Helper\Root;

use Hebis\Csl\MarcConverter\Converter;
use Hebis\Csl\Model\Layout\CslRecord;
use Seboettg\CiteProc\CiteProc;
use Seboettg\CiteProc\StyleSheet;
use Zend\Config\Config;

/**
 * Class Citation
 * @package Hebis\View\Helper\Root
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class Citation extends \VuFind\View\Helper\Root\Citation
{
    /**
     * Citation details
     *
     * @var array
     */
    protected $details = [];

    /**
     * Record driver
     *
     * @var \VuFind\RecordDriver\AbstractBase
     */
    protected $driver;

    /**
     * Date converter
     *
     * @var \VuFind\Date\Converter
     */
    protected $dateConverter;

    /**
     * @var CiteProc
     */
    protected $citeProc;

    /**
     * @var CslRecord;
     */
    protected $cslRecord;

    protected $citationFormats;

    protected $styles = [];

    /**
     * Constructor
     *
     * @param \VuFind\Date\Converter $converter Date converter
     * @param Config $config
     */
    public function __construct(\VuFind\Date\Converter $converter, Config $config)
    {
        parent::__construct($converter);
        $this->dateConverter = $converter;
        $this->citationFormats = array_map("trim", explode(",", $config->Record->citation_formats));

    }

    /**
     * Store a record driver object and return this object so that the appropriate
     * template can be rendered.
     *
     * @param \VuFind\RecordDriver\Base $driver Record driver object.
     *
     * @return Citation
     */
    public function __invoke($driver)
    {
        $this->driver = $driver;
        $this->cslRecord = Converter::convert($driver);
        return $this;
    }

    /**
     * Retrieve a citation in a particular format
     *
     * Returns the citation in the format specified
     *
     * @param string $format Citation format ('APA' or 'MLA')
     *
     * @return string        Formatted citation
     */
    public function getCitation($format)
    {
        $lang = $this->getView()->layout()->userLang;
        $format = strtolower($format);
        $style = StyleSheet::loadStyleSheet($format);
        $citeProc = new CiteProc($style, $this->langCode($lang));
        $rendered = $citeProc->render([json_decode($this->cslRecord)]);

        return $rendered;

    }

    public function getCitationStyleName($format)
    {
        $format = strtolower($format);
        $style = StyleSheet::loadStyleSheet($format);
        $citeProc = new CiteProc($style);
        $citeProc->init();
        $info = CiteProc::getContext()->getInfo();
        return $info->getTitle();
    }

    public function getFormats()
    {
        return $this->citationFormats;
    }

    private function langCode($lang)
    {
        $langBase = array(
            "af" => "af-ZA",
            "ar" => "ar-AR",
            "bg" => "bg-BG",
            "ca" => "ca-AD",
            "cs" => "cs-CZ",
            "da" => "da-DK",
            "de" => "de-DE",
            "el" => "el-GR",
            //"en" => "en-GB",
            "en" => "en-US",
            "es" => "es-ES",
            "et" => "et-EE",
            "fa" => "fa-IR",
            "fi" => "fi-FI",
            "fr" => "fr-FR",
            "he" => "he-IL",
            "hu" => "hu-HU",
            "is" => "is-IS",
            "it" => "it-IT",
            "ja" => "ja-JP",
            "km" => "km-KH",
            "ko" => "ko-KR",
            "mn" => "mn-MN",
            "nb" => "nb-NO",
            "nl" => "nl-NL",
            "nn" => "nn-NO",
            "pl" => "pl-PL",
            "pt" => "pt-PT",
            "ro" => "ro-RO",
            "ru" => "ru-RU",
            "sk" => "sk-SK",
            "sl" => "sl-SI",
            "sr" => "sr-RS",
            "sv" => "sv-SE",
            "th" => "th-TH",
            "tr" => "tr-TR",
            "uk" => "uk-UA",
            "vi" => "vi-VN",
            "zh" => "zh-CN",
        );

        return $langBase[$lang];
    }
}
