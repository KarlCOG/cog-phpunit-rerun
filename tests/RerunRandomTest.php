<?php
/**
 * Created by PhpStorm.
 * Date: 3.07.15
 * Time: 15:20
 *
 * @author Karl Klein <karl.klein@cashongo.co.uk>
 */

class RerunRandomTest extends PHPUnit_Framework_TestCase {

    public function random() {
        $this->assertTrue(rand(0, 1) > 0.5, 'you fail');
    }


    public function testOne()
    {
        $this->random();
    }

    public function testTwo()
    {
        $this->random();
    }

    public function testThree()
    {
        $this->random();
    }

    public function testFour()
    {
        $this->random();
    }

    public function testFive()
    {
        $this->random();
    }
}
