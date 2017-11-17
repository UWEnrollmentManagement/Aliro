<?php

namespace UWDOEM\Group\Test;

use UWDOEM\Connection\Connection;

class MockConnection extends Connection
{
    /**
     * @param string       $baseUrl
     * @param string       $sslKey
     * @param string       $sslCert
     * @param string|null  $sslKeyPassword
     * @param boolean|null $verbose
     * @param array        $options
     * @throws \Exception If the provided $sslKey or $sslCert paths are not valid.
     */
    public function __construct(
        $baseUrl,
        $sslKey,
        $sslCert,
        $sslKeyPassword = null,
        $verbose = false,
        array $options = []
    ) {

        $this->baseUrl = $baseUrl;

        // Get cURL resource
        $this->curl = curl_init();

        // Set cURL parameters
        $this->addOptions(
            [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSLKEY => $sslKey,
                CURLOPT_SSLCERT => $sslCert,
            ]
        );

        if ($sslKeyPassword !== null) {
            $this->addOptions([
                CURLOPT_SSLKEYPASSWD => $sslKeyPassword,
            ]);
        }

        $this->addOptions($options);

        if ($verbose === true) {
            /** @var  $logResource */
            $this->logFile = fopen('php://temp', 'w+');

            $this->addOptions(
                [
                    CURLOPT_VERBOSE => 1,
                    CURLOPT_STDERR => $this->logFile,
                ]
            );
        }
    }
    /**
     * @return mixed
     * @throws \Exception If cURL encounters an error.
     */
//    protected function exec()
//    {
//        $this->addXUwActAs();
//
//        curl_setopt_array($this->curl, $this->options);
//
//        $resp = $this->doExec();
//
//        if (curl_errno($this->curl) !== 0) {
//            $errorText = 'Request Error:' . curl_error($this->curl);
//
//            if ($this->logFile !== null) {
//                rewind($this->logFile);
//                $errorText .= " " . stream_get_contents($this->logFile);
//            }
//
//            throw new \Exception($errorText);
//        }
//        return $resp;
//    }

//    public function getCurl()
//    {
//        return $this->curl;
//    }

//    public function getOptions()
//    {
//        return $this->options;
//    }

    protected function makeSlug($url)
    {
        $url = str_replace([$this->baseUrl], [""], $url);
        $url = str_replace(["?", "&", "/", ".", "="], ["-q-", "-and-", "-", "-", "-"], $url);

        if (strlen($url) > 63) {
            $url = md5($url);
        }

        return $url;
    }

    protected function doExec()
    {
        $url = curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
        $info = curl_getinfo($this->curl);
        $data = file_get_contents(getcwd() . "/test/responses/{$this->makeSlug($url)}");
        return new \UWDOEM\Connection\ConnectionReturn($data, $info);
    }
}
