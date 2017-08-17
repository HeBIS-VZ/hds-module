<?php

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;

class StaticPost extends Gateway
{

    public function __construct($rowClass = 'VuFind\Db\Row\StaticPost')
    {
        parent::__construct('static_post', $rowClass);
    }


    public function createStaticPage()
    {
        $page = $this->createRow();
        //$page->createDate = (new \DateTime('now'))->format()

        return $page;

    }

    public function getPost($id)
    {
        $staticPostRow = $this->select(['id' => $id])->current();

        if (!$staticPostRow) {
            throw new \Exception("Could not find post $id");
        }

        return $staticPostRow;
    }


    /** gets all the rows in the table
     *
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getAll()
    {
        return $this->select();
    }



}