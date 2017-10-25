<?php

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Validator\Between;

class StaticPost extends Gateway
{

    public function __construct($rowClass = 'Hebis\Db\Row\StaticPost')
    {
        parent::__construct('static_post', $rowClass);
    }

    public function getPost($id)
    {
        $staticPostRow = $this->select(['uid' => $id])->current();

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

        $select = $this->sql->select();
        $select->columns([new Expression('MAX(pid)')]);
        $resultSet = $this->select($select)->current();

        $lastpid = $resultSet->pid;

        return $lastpid;

    }

    public function getNav($lang = 'en', $visibilty = true)
    {
        return $this->select(['language' => $lang, 'visible' => intval($visibilty)]);

    }
}