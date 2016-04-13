<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 07.03.16
 * Time: 23:41
 */

namespace Hebis\View\Helper;


class SingleRecordOtherEditionEntryTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordOtherEditionEntry";
        $this->testResultField = "other_edition_entry";
        $this->testRecordIds = [
            'HEB046828966',
            'HEB120899825',
        ];

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

        $transEsc = $this->getMock('VuFind\View\Helper\Root\TransEsc');

        return [
            'basepath' => $basePath,
            'transesc' => $transEsc
        ];
    }
}
