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

class SingleRecordHostItemEntryTest extends AbstractViewHelperTest
{
    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordHostItemEntry";
        $this->testResultField = "";
        $this->testRecordIds = [];

        $this->testSheetName = "In";
        parent::setUp();
    }

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



        $ppnLink = $this->getMock('Hebis\View\Helper\Hebisbs3\PpnLink');

        //invoke method returns "$this"
        $ppnLink->expects($this->any())->method('__invoke')
            ->will($this->returnValue($ppnLink));

        $ppnLink->expects($this->any())->method('getLink')
            ->will($this->returnValue("bar"));

        $multipartItems = $this->getMock('Hebis\View\Helper\Hebisbs3\MultipartItems');

        $multipartItems->expects($this->any())->method('__invoke')
            ->will($this->returnValue($multipartItems));
        $multipartItems->expects($this->any())->method('renderShowAllVolumesLink')
            ->will($this->returnValue("show_all_volumes"));

        return [
            'multipartItems' => $multipartItems,
            'ppnLink' => $ppnLink,
            'basepath' => $basePath,
        ];
    }
}
