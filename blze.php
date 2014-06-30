<?php

require_once "BlazeMeter.php";
require_once "NewRelic.php";
require_once "PerformanceTester.php";

define('USER_ID', getenv('USER_ID'));
define('TEST_ID', getenv('TEST_ID'));

define('NEWRELIC_API_KEY', getenv('NEWRELIC_API_KEY'));
define('NEWRELIC_APPLICATION_ID', getenv('NEWRELIC_APPLICATION_ID'));


$blaze = new BlazeMeter(USER_ID, TEST_ID);

$tester = new PerformanceTester($blaze);

// run actual test
$tester->run();


// gather metrics from NewRelic
$newRelic = new NewRelic(NEWRELIC_API_KEY, NEWRELIC_APPLICATION_ID);

$newRelic->run();
