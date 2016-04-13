<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 11.03.16
 * Time: 14:10
 */

namespace Hebis\View\Helper;


class SingleRecordMainEntryPersonalNameTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordMainEntryPersonalName";
        $this->testRecordIds = [
            'HEB208326162',
            'HEB204713803',
            'HEB053421744'
        ];
        $this->testResultField = 'main_entry_personal_name';
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