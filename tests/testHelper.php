<?php
/**
 * Created by PhpStorm.
 * User: cog_intern
 * Date: 7.07.15
 * Time: 16:24
 */

class testHelper extends PHPUnit_Framework_TestCase {

    public function testOne()
    {
        $this->assertTrue(true);
    }

    public function testTwo()
    {
        $this->assertTrue(true);
    }

    public function testThree()
    {
        $this->assertTrue(false);
    }

    public function testFour()
    {
        $this->assertTrue(true);
    }

    public function testFive()
    {
        $this->assertTrue(false);
    }
}
