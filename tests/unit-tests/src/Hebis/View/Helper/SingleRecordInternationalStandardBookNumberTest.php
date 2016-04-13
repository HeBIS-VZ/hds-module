<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 12.03.16
 * Time: 13:40
 */

namespace Hebis\View\Helper;


class SingleRecordInternationalStandardBookNumberTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordInternationalStandardBookNumber";
        $this->testRecordIds = [
            'HEB312333870',
            'HEB312455240',
            'HEB105439231'
        ];
        $this->testResultField = 'isbn';

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