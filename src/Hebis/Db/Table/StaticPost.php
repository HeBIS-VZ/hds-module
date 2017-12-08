<?php

namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Db\Exception\UnexpectedValueException;
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
            throw new \Exception("Could not find post $pid");
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
        $select = $this->sql->select();
        $select->where(['language' => $lang, 'visible' => intval($visibilty)]);
        $resultSet = $this->executeSelect($select);
        $rowSet = $resultSet;
        return $rowSet;
    }

    /**
     * get Page by di
     * @param $pid
     * @return \Zend\Db\ResultSet\ResultSet
     * @throws UnexpectedValueException
     */
    public function getPagebyId($pid)
    {
        $select = $this->sql->select();
        $select->where(['pid' => $pid]);
        $resultSet = $this->executeSelect($select);

        if ($resultSet->count() < 1) {
            throw new UnexpectedValueException("Could not find rows with PID $pid.");
        }
        return $resultSet;
    }

    public function getPostByPidAndLang($pid, $lang = "en") {
        $select = $this->sql->select();
        $select->where(['pid' => $pid, 'language' => $lang]);
        $resultSet = $this->executeSelect($select);
        $rowSet = $resultSet;
        return $rowSet;
    }
}