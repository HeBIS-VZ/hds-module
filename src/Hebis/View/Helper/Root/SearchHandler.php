<?php
/*
 * IBW3Interface is an interface which communicates with a PICA LBS
 * Copyright (c) 2017 HeBIS-Verbundzentrale, Frankfurt am Main (http://www.hebis.de)
 *
 * vufind is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or any later version.
 *
 * vufind is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Hebis\View\Helper\Root;

use VuFind\Search\Base\Options;
use Zend\Config\Config;
use Zend\View\Helper\AbstractHelper;

class SearchHandler extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function __invoke($searchClass)
    {
        return $this->view->translate($this->config[$searchClass]);
    }
}