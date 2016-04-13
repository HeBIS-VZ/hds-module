<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 11.03.16
 * Time: 13:09
 */

namespace Hebis\View\Helper;


class SingleRecordFestschriftTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordFestschrift";
        $this->testRecordIds = ['HEB363755683'];
        $this->testResultField = 'festschrift_for';

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