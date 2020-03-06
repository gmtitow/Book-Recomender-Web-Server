<?php
namespace App\Libs;

class CurlSender
{

    private $curl = null;
    private $log_file = null;
    private $prev_url = null;

    private $add_referer = true;
    private $set_user_agent = true;

    private $user_agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 YaBrowser/19.9.1.236 Yowser/2.5 Safari/537.36';

    protected $default_headers = [
        "cache-control: max-age=0",
        "Content-Type: text/html; charset=UTF-8",
        "Connection: keep-alive",
        "User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 YaBrowser/19.9.1.236 Yowser/2.5 Safari/537.36",
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
        "Accept-Encoding: gzip, deflate, sdch",
        "Accept-Language: ru,en;q=0.9"
    ];

    function init($log_file = null, $start_referrer = 'https://yandex.ru/') {
        $this->curl = curl_init();

        $this->prev_url = $start_referrer;

        curl_setopt_array($this->curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 86400,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $this->default_headers
        ));

        $this->log_file = $log_file;
    }

    function setHeaders(array $headers) {
        $this->default_headers = $headers;
    }

    function setOptionAddReferer($option) {
        $this->add_referer = $option;
    }

    function setOptionAddUserAgent($option) {
        $this->set_user_agent = $option;
    }

    function resetReferrer($new_referrer = null) {
        $this->prev_url = $new_referrer;
    }

    function send($url)
    {
        curl_setopt($this->curl,CURLOPT_URL,$url);

        $headers = $this->default_headers;

        $host_pattern = "#https?://(.*?)/.*#";
        $host_match = null;
        $matches = preg_match($host_pattern,$url,$host);

        if($matches) {
            $host = $host_match[1];
            $headers[] = "Host: ".$host;
        }

        if($this->set_user_agent) {
            curl_setopt($this->curl,CURLOPT_USERAGENT,$this->user_agent);
        }

        if($this->prev_url!=null && $this->add_referer) {
            curl_setopt($this->curl,CURLOPT_REFERER,$this->prev_url);
        }

        $response = curl_exec($this->curl);

        if ($this->log_file != null) {
            writeInLog($this->log_file, "Got result");
        }

        $err = curl_error($this->curl);

        if ($err) {
            if ($this->log_file != null) {
                $message = "cURL Error #:" . $err;
                writeInLog($this->log_file, $message);
            }
            throw new \Exception("Problem with request to books");
        } else {
            $this->prev_url = $url;
//        if ($log_file != null)
//            writeInLog($log_file, "body: \n" . $response);
        }

        return $response;
    }
}