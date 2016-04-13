<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 03.03.16
 * Time: 17:29
 */

namespace Hebis\View\Helper;

class SingleRecordPublicationDistributionTest extends AbstractViewHelperTest
{

    public function setUp() {
        $this->viewHelperClass = "SingleRecordPublicationDistribution";
        $this->testRecordIds = [
            'HEB303016353',
            'HEB192530127',
            'HEB053561333',
            'HEB130079464',
            'HEB047119578',
            'HEB301709858'
        ];
        $this->testResultField = 'publication_distribution';

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
