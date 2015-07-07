<?php
/**
 * Created by PhpStorm.
 * Date: 29.06.15
 * Time: 9:23
 *
 * listens for and collects failed tests
 * in the end passes them on to be written to cache
 *
 * @author Karl Klein <karl.klein@cashongo.co.uk>
 */

class PHPUnit_Framework_FailureListener extends PHPUnit_Framework_BaseTestListener {

    private $failedTests = array();

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
        $this->failedTests[] = array(
            'testMethodName' => $test->getName(),
            'testClassName'  => get_class($test)
        );
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {

//      if there were failed tests hand off to CacheUtil
        if (! empty($this->failedTests)) {
            $cache = new CacheUtil;
            $cache->createCache($this->failedTests, $_SERVER['argv']);
        }
    }
}