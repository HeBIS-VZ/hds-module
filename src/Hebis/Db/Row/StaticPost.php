<?php


namespace Hebis\Db\Row;

use VuFind\Db\Row\RowGateway;

/**
 * Class StaticPost
 * @package Hebis\Db\Row
 * @author Roshak Zarhoun <roshakz@gmail.com>
 */
class StaticPost extends RowGateway
{


    public function __construct($adapter)
    {

        parent::__construct('uid', 'static_post', $adapter);
    }

}