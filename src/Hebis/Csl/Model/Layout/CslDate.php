<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.17
 * Time: 10:32
 */

namespace Hebis\Csl\Model\Layout;


interface CslDate
{
    /**
     * array with order year, month, day
     * e.g.: [[2012],[6],[17]]
     *
     * @return array
     */
    public function getDateParts();

    /**
     * @return string
     */
    public function getLiteral();

    /**
     * @return string
     */
    public function getCirca();

    /**
     * @return string
     */
    public function getSeason();

    /**
     * @return string
     */
    public function getRaw();

}