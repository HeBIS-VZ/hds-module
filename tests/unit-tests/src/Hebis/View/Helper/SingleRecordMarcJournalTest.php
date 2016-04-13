<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 03.03.16
 * Time: 17:29
 */

namespace Hebis\View\Helper;

class SingleRecordMarcJournalTest extends AbstractViewHelperTest
{
    public function setUp() {

        $this->viewHelperClass = "SingleRecordMarcJournal";
        $this->testRecordIds = [
            'HEB20815342X',
            'HEB246164956',
            'HEB272226211',
            'HEB233323775'
        ];
        $this->testResultField = 'marc_journal';

        parent::setUp(); 
    }

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
