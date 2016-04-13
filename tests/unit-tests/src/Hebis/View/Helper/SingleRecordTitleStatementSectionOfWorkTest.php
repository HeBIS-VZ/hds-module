<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 11.03.16
 * Time: 15:22
 */

namespace Hebis\View\Helper;


class SingleRecordTitleStatementSectionOfWorkTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordTitleStatementSectionOfWork";
        $this->testRecordIds = [
            'HEB047696362'
        ];
        $this->testResultField = 'title_section_of_work';
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