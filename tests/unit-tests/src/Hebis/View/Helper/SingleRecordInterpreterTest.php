<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 03.03.16
 * Time: 17:29
 */

namespace Hebis\View\Helper;

class SingleRecordInterpreterTest extends AbstractViewHelperTest
{
    public function setUp() {

        $this->viewHelperClass = "SingleRecordInterpreter";
        $this->testRecordIds = ['HEB277913128'];
        $this->testResultField = 'interpreter';

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

        return [
            'basepath' => $basePath
        ];
    }
}
