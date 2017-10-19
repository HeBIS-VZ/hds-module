<?php


namespace Hebis\Db\Row;

use VuFind\Db\Row\RowGateway;

/**
 * Class StaticPost
 * @package Hebis\Db\Row
 * @author Roshak Zarhoun <roshak.zarhoun@stud.tu-darmstadt.de>
 */
class StaticPost extends RowGateway
{


    public function __construct($adapter)
    {
        $this->_primary = ['page_id', 'language'];
        parent::__construct('static_post', $adapter);
    }



}