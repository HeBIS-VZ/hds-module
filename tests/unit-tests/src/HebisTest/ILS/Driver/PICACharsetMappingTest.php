<?php

/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.16
 * Time: 11:35
 */

namespace HebisTest\ILS\Driver;

use Hebis\ILS\Driver\PICACharsetMapping;

/**
 * Class PICACharsetMappingTest
 * @package HebisTest\ILS\Driver
 *
 */
class PICACharsetMappingTest extends \PHPUnit_Framework_TestCase
{

    /**
     */
    public function testToUTF8()
    {
        /* Test Á */

        $bytesAcute = [226, 65];
        $str = vsprintf(str_repeat('%c', count($bytesAcute)), $bytesAcute);
        $actual = PICACharsetMapping::toUTF8($str);
        $expected = "Á";
        $this->assertEquals($actual, $expected);

        /* Test Ẽ */
        $bytesTilde = [228, 69];
        $str = vsprintf(str_repeat('%c', count($bytesTilde)), $bytesTilde);
        $actual = PICACharsetMapping::toUTF8($str);
        $expected = "Ẽ";
        $this->assertEquals($actual, $expected);

        /* Test ẼmÁî */
        $bytesTilde = [228, 69, 109, 226, 65, 227, 105];
        $str = vsprintf(str_repeat('%c', count($bytesTilde)), $bytesTilde);
        $actual = PICACharsetMapping::toUTF8($str);
        $expected = "ẼmÁî";
        $this->assertEquals($actual, $expected);

    }
}