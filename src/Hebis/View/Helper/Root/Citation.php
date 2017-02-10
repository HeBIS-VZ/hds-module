<?php
/**
 * Citation view helper
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace Hebis\View\Helper\Root;
use AcademicPuma\CiteProc\CiteProc;
use Hebis\Csl\MarcConverter\Converter;
use Hebis\Csl\Model\Layout\CslRecord;
use Hebis\Csl\MarcConverter\ArticleConverter;
use VuFind\Exception\Date as DateException;
use Zend\Config\Config;
/**
 * Citation view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
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
        $format = strtolower($format);

        $rendered = $this->loadStyleSheet($format)->render(json_decode($this->cslRecord), "bibliography");

        return $rendered;

    }

    public function getCitationStyleName($format)
    {
        $info = $this->loadStyleSheet($format)->getInfo();
        return $info->getTitle();
    }

    public function getFormats()
    {
        return $this->citationFormats;
    }

    /**
     * @param $format
     * @return CiteProc
     */
    private function loadStyleSheet($format) {

        if (!array_key_exists($format, $this->styles)) {
            $styleSheet = CiteProc::loadStyleSheet($format);
            $this->styles[$format] = new CiteProc($styleSheet, "de");
        }
        return $this->styles[$format];
    }
}
