<?php
/**
 * Created by PhpStorm.
 * User: cog_intern
 * Date: 9.07.15
 * Time: 10:45
 */

class CacheUtilTest extends PHPUnit_Framework_TestCase {

    public function testCreateAndReadCache()
    {
        $cache = new CacheUtil;
        $test1 = new RerunRandomTest();
        $test2 = new RerunRandomTest();
        $failedTestsToWrite = array(
            array(
                'testMethodName' => $test1->getName(),
                'testClassName'  => get_class($test1)
            ),
            array(
                'testMethodName' => $test2->getName(),
                'testClassName'  => get_class($test2)
            )
        );
        $cmdOptions = array('phpunit', 'testName');
        $cache->createCache($failedTestsToWrite, $cmdOptions);


        $key = $cache->generateKey($cmdOptions);
        $failedTestsFromFile = $cache->readCache($key);

        $this->assertEquals($failedTestsToWrite, $failedTestsFromFile, 'Test written to file differ from those read from file');
    }
}
