<?php

namespace App\Libraries;

use Error;

class URLService {
    private $ch;
    private $response;
    private $maxRetry;

    public $info;
    public $http_code;

    /**
     * class constructor.
     * @param array $options curl options.
     * @param int $maxRetry
     */
    public function __construct(array $options, int $maxRetry = 3)
    {
        $this->ch = curl_init();
        $this->maxRetry = $maxRetry;

        if (!$options || ! is_array($options)) {
            throw new Error('You must define Curl option(s) as an array!');
        }

        curl_setopt_array($this->ch, $options);
    }

    /**
     * call to execute the curl instance.
     * @return mixed
     */
    public function exec()
    {
        $this->execWithRetry();
        $this->info = curl_getinfo($this->ch);
        $this->http_code = $this->info['http_code'];
        // close request connection.
        curl_close($this->ch);

        return $this->response;
    }

    /**
     * Execute curl instance internally with retries.
     * @return void
     */
    private function execWithRetry()
    {
        $retry = 0;
        while ($retry++ < $this->maxRetry) {
            $this->response = curl_exec($this->ch);

            if ($this->response) {
                break;
            }
            sleep($retry * $retry);
            $retry++;
        }
    }
}
