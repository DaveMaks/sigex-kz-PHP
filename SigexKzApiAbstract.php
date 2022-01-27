<?php

namespace Sigexkz;

use GuzzleHttp\Client;

define('BASE_SIGEXKZ_URL', 'https://sigex.kz/');

abstract class SigexKzApiAbstract
{
    /** @var /GuzzleHttp/Client
     */
    static $client;
    var $Content_Type = 'application/json';

    function __construct()
    {
        if (self::$client == null)
            self::$client = new Client([
                'base_uri' => BASE_SIGEXKZ_URL,
                'timeout' => 5,
            ]);
    }

    function get($url, $param = array(), $asObject = false)
    {
        $reqest = self::$client->request('GET', ((!empty($url)) ? 'api/' . $url : 'api'), [
            'query' => (!empty($param) && is_array($param)) ? $param : null,
            'verify' => true,
            'allow_redirects' => false,
            //'body' => null,
            'headers' => [
                'Content-Type' => $this->Content_Type
            ]
        ]);
        if ($reqest->getStatusCode() == 200) {
            return ($asObject) ? json_decode($reqest->getBody()->getContents()) : $reqest->getBody()->getContents();
        }
        return null;
    }

    function post($url, $param = array(), $asObject = false)
    {
        $options = [
            //'query' => (!empty($param) && is_array($param)) ? $param : null,
            'verify' => true,
            'allow_redirects' => false,
            'headers' => [
                'Content-Type' => $this->Content_Type
            ]
        ];

        /// TODO передаелать это порно
        if (is_string($param)) {
            $options['body'] = $param;
        }
        if (is_array($param) || is_object($param)) {
            $options['json'] = $param;
        }

        $reqest = self::$client->request('POST', ((!empty($url)) ? 'api/' . $url : 'api'), $options);

        if ($reqest->getStatusCode() == 200) {
            return ($asObject) ? json_decode($reqest->getBody()->getContents()) : $reqest->getBody()->getContents();
        }
        return null;
    }

    private function Request($metod, $url, $param = array(), $Content_Type = 'application/json')
    {
        // return $client->request($metod, 'http://httpbin.org?foo=bar');
    }

}