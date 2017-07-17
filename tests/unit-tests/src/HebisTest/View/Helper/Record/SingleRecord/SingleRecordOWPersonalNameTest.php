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

namespace HebisTest\View\Helper\Record\SingleRecord;

use HebisTest\View\Helper\Record\AbstractViewHelperTest;

/**
 * Class SingleRecordOWTitleStatementTest
 * @package Hebis\View\Helper\Record
 *
 * @author Claudia Grote <grote@hebis.uni-frankfurt.de>
 */
class SingleRecordOWPersonalNameTest extends AbstractViewHelperTest
{
    public function setUp()
    {

        $this->viewHelperClass = "SingleRecordOWPersonalName";
        $this->testRecordIds = [];
        $this->testResultField = '';
        $this->testSheetName = "Originalschrift Verantwortlich";
        parent::setUp();
    }

    // So, hier fehlen jetzt aber noch substanzielle Tests.

    /**
     * Get plugins to register to support view helper being tested
     *
     * @return array
     */
    protected function getPlugins()
    {
        $basePath = $this->getMock('Zend\View\Helper\BasePath');
        $basePath->expects($this->any())->method('__invoke')
            ->will($this->returnValue('/vufind2'));
        $url = $this->getMock('Zend\View\Helper\Url');
        $url->expects($this->any())->method('__invoke')
            ->will($this->returnValue("/foobar"));

        return [
            'url' => $url,
            'basepath' => $basePath
        ];
    }
}