<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 08.03.16
 * Time: 17:30
 */

namespace Hebis\View\Helper;


class SingleRecordSubjectAddedKeywordsTest extends AbstractViewHelperTest
{
    public function setUp()
    {
        $this->viewHelperClass = "SingleRecordSubjectAddedKeywords";
        $this->testResultField = "subject_added_keywords";
        $this->testRecordIds = [
            'HEB104075848',
            'HEB185769748',
            'HEB104076410',
            'HEB303357886',
            'HEB212739271',
            'HEB274180618',
            'HEB289661153',
            'HEB061371955',
            'HEB107443414',
            'HEB04567986X',
            'HEB110911911',
            'HEB072273666',
            'HEB306409275',
            'HEB109899237'
        ];

        parent::setUp();
    }

    /*
    public function test__invoke()
    {

        foreach ($this->testRecordIds as $k) {

            if (!array_key_exists($k, $this->expections)) {
                continue;
            }
            if (!array_key_exists($this->testResultField, $this->expections[$k])) {
                continue;
            }

            $this->assertEquals(
                $this->expections[$k][$this->testResultField],
                implode(" ; ", $this->viewHelper->__invoke($this->fixtures[$k])), 'Testing "' . $this->viewHelperClass . '" using "' . $k . '.json"');
        }

    }
    */
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