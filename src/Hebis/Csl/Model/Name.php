<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.17
 * Time: 11:25
 */

namespace Hebis\Csl\Model;


use Hebis\Csl\Model\Layout\CslName;
use stdClass;
use Zend\Stdlib\JsonSerializable;

class Name implements CslName, JsonSerializable
{

    use JsonSerializeTrait;

    private $given;

    private $droppingParticle;

    private $nonDroppingParticle;

    private $family;

    private $suffix;

    private $jsonFormat;

    /**
     * @return mixed
     */
    public function getGiven()
    {
        return $this->given;
    }

    /**
     * @param mixed $given
     */
    public function setGiven($given)
    {
        $this->given = $given;
    }

    /**
     * @return mixed
     */
    public function getDroppingParticle()
    {
        return $this->droppingParticle;
    }

    /**
     * @param mixed $droppingParticle
     */
    public function setDroppingParticle($droppingParticle)
    {
        $this->droppingParticle = $droppingParticle;
    }

    /**
     * @return mixed
     */
    public function getNonDroppingParticle()
    {
        return $this->nonDroppingParticle;
    }

    /**
     * @param mixed $nonDroppingParticle
     */
    public function setNonDroppingParticle($nonDroppingParticle)
    {
        $this->nonDroppingParticle = $nonDroppingParticle;
    }

    /**
     * @return mixed
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param mixed $family
     */
    public function setFamily($family)
    {
        $this->family = $family;
    }

    /**
     * @return mixed
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param mixed $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }


}