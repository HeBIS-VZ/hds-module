<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 03.03.16
 * Time: 17:29
 */

namespace Hebis\View\Helper;

class SingleRecordTitleStatementTest extends AbstractViewHelperTest
{
    public function setUp() {

        $this->viewHelperClass = "SingleRecordTitleStatement";
        $this->testRecordIds = [
            'HEB078893151',
            'HEB303016353',
            'HEB307231089',
            'HEB181035510',
            'HEB025023241',
            'HEB01652537X',
            'HEB051363135',
            'HEB047883103',
            'HEB051025639',
            'HEB051363135',
            'HEB23305796X',
            'HEB047883103'
        ];
        $this->testResultField = 'title';

        parent::setUp(); 
    }

    public function testRemoveSpecialChars() {

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

        return [
            'basepath' => $basePath
        ];
    }
}
