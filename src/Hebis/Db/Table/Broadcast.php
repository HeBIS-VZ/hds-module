<?php


namespace Hebis\Db\Table;

use VuFind\Date\Converter;
use VuFind\Db\Table\Gateway;
use VuFind\View\Helper\Root\DateTime;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Where;
use Zend\Http\Header\Date;
use Zend\Http\PhpEnvironment\Request;

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
    public function getBroadcastsById($bcid, $lang = null)
    {
        $select = $this->sql->select();
        $where = ['bcid' => $bcid];
        if ($lang !== null) {
            $where['language'] = $lang;
        }
        $select->where($where);
        $resultSet = $this->executeSelect($select);
        return $resultSet;
    }

    /** returns a all broadcasts filtered by
     * @param string $lang language of broadcast
     * @param String $show determines wether show or not
     * @param bool $outOfDate
     * @return \Zend\Db\ResultSet\ResultSet
     * @internal param String $type type (color) of broadcast alert
     * @internal param bool $expired due date past
     */
    public function getAllByParameter($lang = 'en', $hide = null, $outOfDate = false)
    {
        $select = $this->sql->select();
        $where = new Where();
        //$select->where(['language'=>$lang, 'show'=> $show, 'type'=> $type]);

        $where->equalTo('language', $lang);

        if ($hide !== null) {
            $where->equalTo('hide', $hide);
        }
        $now = date('Y-m-d H:i:s', strtotime(date("Y-m-d") . " 00:00:00"));

        if (!$outOfDate) {

            $where
                ->NEST
                ->lessThanOrEqualTo('startDate', $now)
                ->and
                ->greaterThanOrEqualTo('expireDate', $now)
                ->UNNEST;
        } else {
            $where
                ->NEST
                ->greaterThan('startDate', $now)
                ->or
                ->lessThan('expireDate', $now)
                ->UNNEST;
        }

        $select->where($where);
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


    public function persist(Request $request)
    {

        $params = $request->getPost();

        $rowSet = $this->getBroadcastsById($params['bcid']);
        $p = $params->toArray();
        foreach ($params['bc-lang'] as $langKey => $lang) {#

            if ($rowSet->count() === 0) { //add message
                if ($langKey === 0) {
                    $bcid = $this->getLastBcId() + 1;
                }
                $row = $this->createRow();
                $row->language = $lang;
                $row->bcid = $bcid;
            } else { // edit message
                $bcid = $params['bcid'];
                $row = $this->getBroadcastsById($bcid, $lang)->current();
            }
            $row->type = $params['bc-type'];
            $row->message = $params['bc-message'][$langKey];
            $row->startDate = $this->dateTime($params['bc-startDate']);
            $row->expireDate = $this->dateTime($params['bc-expireDate']);
            $row->hide = intval($params['bc-hide'] == "on");

            $row->save();
        }
    }

    private function dateTime($dateTime)
    {
        $dateTimeConverter = new Converter();
        try {
            return $dateTimeConverter->convertFromDisplayDate('Y-m-d', $dateTime);
        } catch (\VuFind\Exception\Date $e) {
            //TODO: log error
        }
        return null;
    }

}