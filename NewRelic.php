<?php

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
