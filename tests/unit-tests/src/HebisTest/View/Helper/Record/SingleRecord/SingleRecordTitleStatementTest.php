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
 * Class SingleRecordTitleStatementTest
 * @package Hebis\View\Helper\Record
 *
 * @author Sebastian Böttger <boettger@hebis.uni-frankfurt.de>
 */
class SingleRecordTitleStatementTest extends AbstractViewHelperTest
{
    public function setUp()
    {

        $this->viewHelperClass = "SingleRecordTitleStatement";
        $this->testRecordIds = [];
        $this->testResultField = 'title';
        $this->testSheetName = "Titel";
        parent::setUp();
    }

    public function testRemoveSpecialChars()
    {

        $this->assertEquals("The Result of a Equation", $this->viewHelper->removeSpecialChars("@The Result of a Equation"));
        $this->assertEquals("The Result of a Equation", $this->viewHelper->removeSpecialChars("The @Result of a Equation"));
        $this->assertEquals("The Result of a Equation", $this->viewHelper->removeSpecialChars("@The @Result of a Equation"));
        $this->assertEquals("Eine Übersicht ist wichtig", $this->viewHelper->removeSpecialChars("Eine @Übersicht ist wichtig"));
        $this->assertEquals("E-M@il für dich", $this->viewHelper->removeSpecialChars("E-M@il für dich"));
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
        $url = $this->getMock('Zend\View\Helper\Url');
        $url->expects($this->any())->method('__invoke')
            ->will($this->returnValue("/foobar"));

        return [
            'url' => $url,
            'basepath' => $basePath
        ];
    }
}
