<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 12.03.16
 * Time: 12:42
 */

namespace Hebis\View\Helper;


class SingleRecordAdditionalPhysicalFormAvailableNoteTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordAdditionalPhysicalFormAvailableNote";
        $this->testRecordIds = [
            'HEB047322551',
            'HEB047688181',
            'HEB04683429X'
        ];
        $this->testResultField = 'additional_physical_form_available_note';
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