<?php

namespace Hebis\Db\Row;

use VuFind\Db\Row\RowGateway;

class StaticPost extends RowGateway
{

//    protected $config;

    public function __construct($adapter)
    {
        parent::__construct('id', 'static_post', $adapter);
    }


    /**
     * @return ResultSet of all posts in table
     */
    public function fetchAll()
    {
        $allPosts = $this->getDbTable('static_pages')->select();
        return $allPosts;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPost($id)
    {
        $staticPostRow = $this->getDbTable('static_pages')->select(['id' => $id])->current();
        /*if (!$staticPostRow) {
            throw new \Exception("Could not find post $id");
        }*/
        return $staticPostRow;
    }

    public function deletePost($id)
    {
        $this->getDbTable('static_pages')->delete(['id' => (int)$id]);
    }

}