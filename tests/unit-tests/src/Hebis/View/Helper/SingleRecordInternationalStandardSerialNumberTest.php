<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 12.03.16
 * Time: 13:40
 */

namespace Hebis\View\Helper;


class SingleRecordInternationalStandardSerialNumberTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordInternationalStandardSerialNumber";
        $this->testRecordIds = [
            'HEB047084022',
            'HEB048613398'
        ];
        $this->testResultField = 'issn';

        parent::setUp();
    }

    /**
     * Get plugins to register to support view helper being tested
     *
     * @return array
     */
    protected function getPlugins()
    {
        return [];
    }
}