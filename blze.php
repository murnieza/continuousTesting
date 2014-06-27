<?php

define('USER_ID', getenv('USER_ID'));
define('TEST_ID', getenv('TEST_ID'));

define('NEWRELIC_API_KEY', getenv('NEWRELIC_API_KEY'));
define('NEWRELIC_APPLICATION_ID', getenv('NEWRELIC_APPLICATION_ID'));


class BlazeMeter
{
    const API_URL = 'https://a.blazemeter.com/api/rest/blazemeter/';

    protected $userId;

    protected $testId;


    public function __construct($userId, $testId)
    {
        $this->userId = $userId;
        $this->testId = $testId;
    }

    public function getLastArchive()
    {
        return $this->makeCall('testGetArchive');
    }

    public function getTestStatus()
    {
        return $this->makeCall('testGetStatus');
    }

    public function startTest()
    {
        return $this->makeCall('testStart');
    }

    public function getArchive()
    {
        return $this->makeCall('testGetArchive');
    }

    protected function makeCall($endpoint)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $this->generateRequestUrl($endpoint),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]
        );

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    protected function generateRequestUrl($endpoint)
    {
        return self::API_URL . $endpoint . '?user_key=' . $this->userId .
            '&test_id=' . $this->testId;
    }
}


class PerformanceTester
{
    const STATUS_RUNNING = 'Running';

    protected $api;

    public function __construct()
    {
        $this->api = new BlazeMeter(USER_ID, TEST_ID);
    }

    public function run($resultsFileName ='report.jtl')
    {
        if ($this->isRunning()) {
            throw new \LogicException("Performance test already running.");
        } else {
            echo '[ INFO ] No tests currently running. Continuing...' . PHP_EOL;
        }

//        $this->startTest();

        while ($this->isRunning()) {
            echo "Testing in progress..." . PHP_EOL;
            sleep(30);
        }

        $this->saveJtlFile($resultsFileName);

        echo '[ INFO ] Testing done.' . PHP_EOL;
    }

    protected function startTest()
    {
        $this->api->startTest();
    }

    protected function isRunning()
    {
        $status = $this->api->getTestStatus();

        if ($status->status == self::STATUS_RUNNING) {
            return true;
        }

        return false;
    }

    protected function saveJtlFile($fileName)
    {
        $archive = $this->api->getArchive();

        $zipLink = $archive->reports[0]->zip_url;

        $zipName = $fileName . ".zip";

        $this->downloadZip($zipLink, $zipName);

        $zipContent = file_get_contents('zip://' . $fileName .
            '.zip#report.jtl');
        file_put_contents($fileName, $zipContent);
    }

    protected function downloadZip($link, $name)
    {
        $handler = fopen($name, 'wb');

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL  => $link,
                CURLOPT_FILE => $handler,
            ]
        );

        curl_exec($curl);

        fclose($handler);
        curl_close($curl);
    }
}

class NewRelic
{
    protected $apiKey;

    protected $applicationId;

    public function __construct($apiKey, $applicationId)
    {
        $this->apiKey = $apiKey;
        $this->applicationId = $applicationId;
    }

    public function run()
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL  => $this->getUrl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "X-Api-Key:" . $this->apiKey,
                ]
            ]
        );

        $result = curl_exec($curl);

        curl_close($curl);

        if ($object = json_decode($result)) {
            $this->saveMetric($object);
        } else {
            throw new Exception("Wrong result from NewRelic: " . $result);
        }

    }

    protected function getUrl()
    {
        $date = new \DateTime();

        date_sub($date, \date_interval_create_from_date_string('10 minutes'));

        $link = "https://api.newrelic.com/v2/applications/" .
            $this->applicationId . ".json?from=" .
            $date->format(\DateTime::ATOM);

        return $link;
    }

    protected function saveMetric($object)
    {
        file_put_contents(
            'responseTime',
            'YVALUE=' .
                $object->application->application_summary->response_time
        );
    }
}

$tester = new PerformanceTester;

$tester->run();

$newRelic = new NewRelic(NEWRELIC_API_KEY, NEWRELIC_APPLICATION_ID);

$newRelic->run();
