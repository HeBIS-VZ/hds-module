<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 11.03.16
 * Time: 11:36
 */

namespace Hebis\View\Helper;


class SingleRecordDissertationNoteTest extends AbstractViewHelperTest
{

    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordDissertationNote";
        $this->testRecordIds = [
            'HEB226204618',
            'HEB301709858'
        ];
        $this->testResultField = 'dissertation_note';

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