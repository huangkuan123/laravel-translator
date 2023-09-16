<?php

namespace Huangkuan\LaravelTranslator;

class Rep
{
    public $text;
    public $status;
    public $err;
    public $http_code;
    public $json_raw;
    public $rep_headers;

    public function set($text, $status, $err = '', $json_raw = '')
    {
        $this->text = $text;
        $this->status = $status;
        $this->err = $err;
        $this->json_raw = $json_raw;
    }

    public function setBase($headers, $http_code)
    {
        $this->rep_headers = $headers;
        $this->http_code = $http_code;
    }

    public function getResult()
    {
        return [
            'status' => $this->status,
            'text' => $this->text
        ];
    }

    public function getMsg()
    {
        return [
            'http_code' => $this->http_code,
            'err' => $this->err,
            'raw' => $this->json_raw,
            'rep_headers' => $this->rep_headers
        ];
    }
}
