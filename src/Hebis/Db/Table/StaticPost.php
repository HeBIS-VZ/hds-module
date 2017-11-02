<?php

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Db\Sql\Expression;

class StaticPost extends Gateway
{

    public function __construct($rowClass = 'Hebis\Db\Row\StaticPost')
    {
        parent::__construct('static_post', $rowClass);
    }

    public function getPost($pid, $lang = "en")
    {
        $staticPostRow = $this->select(['pid' => $pid, 'language' => $lang])->current();

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

    /**
     * @return mixed
     */
    public function getLastPageID()
    {
        $select = $this->sql->select();

        $select->columns(['lastPid' => new Expression('MAX(pid)')]);

        $resultSet = $this->executeSelect($select);

        $rowset = $resultSet->getDataSource()->current();

        $lastpid = $rowset['lastPid'];

        return $lastpid;

    }

    /**
     * @param string $lang language filter
     * @param bool $visibilty visibility filter
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getNav($lang = 'en', $visibilty = true)
    {
        return $this->select(['language' => $lang, 'visible' => intval($visibilty)]);

    }

    public function getPostByPid($pid)
    {
        $select = $this->sql->select();
        $select->where(['pid' => $pid]);
        $resultSet = $this->executeSelect($select);
        $rowSet = $resultSet;
        return $rowSet;
    }

    public function getPostByPidAndLang($pid, $lang = "en") {
        $select = $this->sql->select();
        $select->where(['pid' => $pid, 'language' => $lang]);
        $resultSet = $this->executeSelect($select);
        $rowSet = $resultSet;
        return $rowSet;
    }
}