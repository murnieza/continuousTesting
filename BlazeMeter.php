<?php

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
