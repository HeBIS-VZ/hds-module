<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 31.01.17
 * Time: 18:25
 */

namespace Hebis\Csl\Model;


use Hebis\Csl\Model\Layout\CslDate;
use Zend\Stdlib\JsonSerializable;

class Date implements CslDate, JsonSerializable
{
    use JsonSerializeTrait;

    private $dateParts;

    private $literal;

    private $circa;

    private $season;

    private $raw;

    /**
     * @param mixed $dateParts
     */
    public function setDateParts($dateParts)
    {
        $this->dateParts = $dateParts;
    }

    /**
     * @param mixed $literal
     */
    public function setLiteral($literal)
    {
        $this->literal = $literal;
    }

    /**
     * @param mixed $circa
     */
    public function setCirca($circa)
    {
        $this->circa = $circa;
    }

    /**
     * @param mixed $season
     */
    public function setSeason($season)
    {
        $this->season = $season;
    }

    /**
     * @param mixed $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }



    /**
     * array with order year, month, day
     * e.g.: [[2012],[6],[17]]
     *
     * @return array
     */
    public function getDateParts()
    {
        return $this->dateParts;
    }

    /**
     * @return string
     */
    public function getLiteral()
    {
        return $this->literal;
    }

    /**
     * @return string
     */
    public function getCirca()
    {
        return $this->circa;
    }

    /**
     * @return string
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }
}