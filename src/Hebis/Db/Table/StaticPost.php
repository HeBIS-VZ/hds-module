<?php

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Db\Sql\Select;
use Zend\Stdlib\ArrayObject;

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

    public function getLastPageIDs()
    {
        $select = $this->sql->select();
        $select->columns(['page_id']);

        return $this->selectWith($select);

    }

    public function getNav($lang = 'en', $visibilty = true)
    {
        return $this->select(['language' => $lang, 'visible' => intval($visibilty)]);

    }
}