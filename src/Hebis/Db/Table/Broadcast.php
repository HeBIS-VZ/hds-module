<?php


namespace Hebis\Db\Table;

use VuFind\Db\Table\Gateway;
use Zend\Db\Sql\Expression;

/**
 * Class Broadcast
 * @package Hebis\Db\Table
 */
class Broadcast extends Gateway
{

    public function __construct($rowClass = 'Hebis\Db\Row\Broadcast')
    {
        parent::__construct('broadcasts', $rowClass);
    }

    /**
     * @param int $bcid broadcast ID
     * @param string $lang language of broadcast
     * @return array|\ArrayObject|null
     */
    public function getBroadcast($bcid, $lang = 'en')
    {
        $select = $this->sql->select();
        $select->where(['bcid' => $bcid, 'language' => $lang]);
        $resultSet = $this->executeSelect($select);
        $broadcastRow = $resultSet->current();
        return $broadcastRow;
    }

    /**
     * @param $bcid int broadcast ID
     * @return \Zend\Db\ResultSet\ResultSet Set of matched broadcasts rows
     */
    public function getBroadcastSetById($bcid)
    {
        $select = $this->sql->select();
        $select->where(['bcid' => $bcid]);
        $resultSet = $this->executeSelect($select);
        return $resultSet;
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSet Set of all broadcasts
     */
    public function getAll()
    {
        return $this->select();
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSet The last broadcast ID
     */
    public function getLastBcId()
    {
        $select = $this->sql->select();
        $select->columns(['lastBcId' => new Expression('MAX(bcid)')]);
        $resultSet = $this->executeSelect($select)->getDataSource()->current();

        return $resultSet['lastBcId'];
    }

    /**
     * @param string $lang language filter
     * @param bool $visibilty visibility filter
     * @return \Zend\Db\ResultSet\ResultSet

    public function getNav($lang = 'en', $visibilty = true)
     * {
     * return $this->select(['language' => $lang, 'visible' => intval($visibilty)]);
     *
     * }*/

}