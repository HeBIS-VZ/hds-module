<?php


namespace Hebis\Db\Row;

use VuFind\Db\Row\RowGateway;

/**
 * Class Broadcast represents broadcast row
 * @package Hebis\Db\Row
 * @author Roshak Zarhoun <roshakz@gmail.com>
 */
class Broadcast extends RowGateway
{
    public function __construct($adapter)
    {
        parent::__construct('uid', 'broadcasts', $adapter);
    }
}