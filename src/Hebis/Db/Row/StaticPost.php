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

    public function getPost($id)
    {
        $staticPostRow = $this->select(['id' => $id])->current();

        if (!$staticPostRow) {
            throw new \Exception("Could not find post $id");
        }

        return $staticPostRow;
    }


}