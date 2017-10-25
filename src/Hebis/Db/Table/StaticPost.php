<?php

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;

class StaticPost extends Gateway
{

    public function __construct($rowClass = 'Hebis\Db\Row\StaticPost')
    {
        parent::__construct('static_post', $rowClass);
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

    public function getLastPageID()
    {

        $lastPID = $this->query('SELECT MAX(page_id) FROM static_post', array(2))->current();

        return $lastPID;

    }

    public function getNav($lang = 'en', $visibilty = true)
    {
        return $this->select(['language' => $lang, 'visible' => intval($visibilty)]);

    }
}