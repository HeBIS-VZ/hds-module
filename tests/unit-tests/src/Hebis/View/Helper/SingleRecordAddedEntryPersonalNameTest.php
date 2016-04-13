<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 11.03.16
 * Time: 12:24
 */

namespace Hebis\View\Helper;


class SingleRecordAddedEntryPersonalNameTest extends AbstractViewHelperTest
{
    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordAddedEntryPersonalName";
        $this->testRecordIds = [
            'HEB300617305',
            'HEB078893151',
            'HEB212696629',
            'HEB095212299'
        ];
        $this->testResultField = 'added_entry_personal_name';
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