--TEST--
php phpunit tests/testHelper.php
--FILE--
<?php
unset($_SERVER['argv']);
$_SERVER['argv'] = array();
$_SERVER['argv'][0] = 'phpunit';
$_SERVER['argv'][1] = 'tests/testHelper.php';

require 'vendor/autoload.php';
Rerun_Command::main();
?>
--EXPECTF--
PHPUnit 4.7.6 by Sebastian Bergmann and contributors.

Runtime:	PHP 5.5.9-1ubuntu4.9
Configuration:	/srv/phpunit-rerun/phpunit.xml

..F.F

Time: %s ms, Memory: %sMb

There were 2 failures:

1) testHelper::testThree
Failed asserting that false is true.

/srv/phpunit-rerun/tests/testHelper.php:23
/srv/phpunit-rerun/src/RerunTestRunner.php:346

2) testHelper::testFive
Failed asserting that false is true.

/srv/phpunit-rerun/tests/testHelper.php:33
/srv/phpunit-rerun/src/RerunTestRunner.php:346

FAILURES!
Tests: 5, Assertions: 5, Failures: 2.