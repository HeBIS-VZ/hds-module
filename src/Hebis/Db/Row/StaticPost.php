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
        parent::__construct('id', 'static_post', $adapter);
    }



}