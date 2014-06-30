<?php

class PerformanceTester
{
    const STATUS_RUNNING = 'Running';

    protected $api;

    public function __construct($blazeMeter)
    {
        $this->api = $blazeMeter;
    }

    public function run($resultsFileName ='report.jtl')
    {
        if ($this->isRunning()) {
            throw new \LogicException("Performance test already running.");
        } else {
            echo '[ INFO ] No tests currently running. Continuing...' . PHP_EOL;
        }

        $this->startTest();

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
