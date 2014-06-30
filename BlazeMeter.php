<?php

/**
 * Class BlazeMeter enables calls to BlazeMeter API
 */
class BlazeMeter
{
    /**
     * Base url for API calls
     */
    const API_URL = 'https://a.blazemeter.com/api/rest/blazemeter/';

    /**
     * @var string unique user identification id
     */
    protected $userId;

    /**
     * @var string  unique test identification id
     */
    protected $testId;

    /**
     * @param $userId   string
     * @param $testId   string
     */
    public function __construct($userId, $testId)
    {
        $this->userId = $userId;
        $this->testId = $testId;
    }

    /**
     * Makes call to testGetArchive endpoint
     *
     * @return mixed
     */
    public function getLastArchive()
    {
        return $this->makeCall('testGetArchive');
    }

    /**
     * Makes call to testGetStatus endpoint
     *
     * @return mixed
     */
    public function getTestStatus()
    {
        return $this->makeCall('testGetStatus');
    }

    /**
     * Makes call to testStart endpoint
     *
     * @return mixed
     */
    public function startTest()
    {
        return $this->makeCall('testStart');
    }

    /**
     * Makes call to testGetArchive endpoint
     *
     * @return mixed
     */
    public function getArchive()
    {
        return $this->makeCall('testGetArchive');
    }

    /**
     * Executes actual API call to specified endpoint
     *
     * @param $endpoint string  Endpoint to send request to
     *
     * @return mixed
     */
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

    /**
     * Glues request URL from base URL, specified endpoint and user/test ids
     *
     * @param $endpoint
     * @return string
     */
    protected function generateRequestUrl($endpoint)
    {
        return self::API_URL . $endpoint . '?user_key=' . $this->userId .
            '&test_id=' . $this->testId;
    }
}
