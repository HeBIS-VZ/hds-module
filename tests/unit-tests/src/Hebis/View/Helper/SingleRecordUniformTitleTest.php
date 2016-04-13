<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 07.03.16
 * Time: 23:41
 */

namespace Hebis\View\Helper;


class SingleRecordUniformTitleTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordUniformTitle";
        $this->testResultField = "uniform_title";
        $this->testRecordIds = [
            'HEB307231089',
            'HEB305235362',
            'HEB311395252',
            'HEB095019537'
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
        return [];
    }
}
