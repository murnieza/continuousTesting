<?php

/**
 * Class PerformanceTester
 */
class PerformanceTester
{
    /**
     * constant for "Running" status
     */
    const STATUS_RUNNING = 'Running';

    /**
     * @var BlazeMeter  api client object
     */
    protected $api;

    /**
     * Sets API client
     *
     * @param $blazeMeter   BlazeMeter
     */
    public function __construct($blazeMeter)
    {
        $this->api = $blazeMeter;
    }

    /**
     * Checks if test is not currently running. If not, starts new test and waits for it's completion.
     * After test calls save of report file
     *
     * @param string $resultsFileName
     *
     * @throws LogicException
     */
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

    /**
     * Sends start command to API
     */
    protected function startTest()
    {
        $this->api->startTest();
    }

    /**
     * Checks current  status of test
     *
     * @return bool
     */
    protected function isRunning()
    {
        $status = $this->api->getTestStatus();

        if ($status->status == self::STATUS_RUNNING) {
            return true;
        }

        return false;
    }

    /**
     * Downloads zip file and extracts reports file
     *
     * @param $fileName string  file name to extract
     */
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

    /**
     * Downloads zip file
     *
     * @param $link string  zip file URL
     * @param $name string  name to save downloaded file under
     */
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
