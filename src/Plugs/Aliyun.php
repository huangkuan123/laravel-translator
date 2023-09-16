<?php

namespace Huangkuan\LaravelTranslator\Plugs;

use Huangkuan\LaravelTranslator\BaseTranslat;
use Huangkuan\LaravelTranslator\Rep;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Aliyun extends BaseTranslat
{

    public $posts = '';

    public function makePosts($inputs)
    {
        $posts = ['FormatType' => 'text'];
        $posts['SourceText'] = $inputs['text'];
        $posts['SourceLanguage'] = $inputs['from'];
        $posts['TargetLanguage'] = $inputs['to'];
        $posts['Scene'] = config('translator.plugs.aliyun.scene');
        $body = json_encode($this->mergeArgs($posts, $inputs['args'], 'posts'));
        $this->posts = $body;
        return ['body' => $body];
    }

    public function makeHeaders($inputs)
    {
        // 1.对body做MD5+BASE64加密
        $bodyMd5 = self::MD5_Base64($this->posts);
//        $bodyMd5 = base64_encode(md5($this->posts));
        // 2.计算 HMAC-SHA1
        $date = self::toGMTString();
        $url = config('translator.plugs.aliyun.url');
        $ak_secret = config('translator.plugs.aliyun.access_secret');
        $ak_id = config('translator.plugs.aliyun.access_key_id');
        $version = config('translator.plugs.aliyun.x_acs_version');
        $uuid = Str::random(10);
        $stringToSign = 'POST' . "\n" . 'application/json' . "\n" . $bodyMd5 . "\n" . 'application/json;chrset=utf-8' . "\n" . $date . "\n"
            . 'x-acs-signature-method:HMAC-SHA1' . "\n"
            . 'x-acs-signature-nonce:' . $uuid . "\n"
            . 'x-acs-version:' . $version . "\n"
            . parse_url($url, PHP_URL_PATH);
//            $signature = hash_hmac('sha1', $stringToSign, $ak_secret, true);
        $signature = self::HMACSha1($stringToSign, $ak_secret);
        // 3.得到 authorization header
        $authHeader = 'acs ' . $ak_id . ':' . ($signature);
        // 构建HTTP请求
        $headers =[
            'Accept' => 'application/json',
            'Content-Type' => 'application/json;chrset=utf-8',
            'Content-MD5' => $bodyMd5,
            'Date' => $date,
            'Host' => parse_url($url, PHP_URL_HOST),
            'Authorization' => $authHeader,
            'x-acs-signature-nonce' => $uuid,
            'x-acs-signature-method' => 'HMAC-SHA1',
            'x-acs-version' => $version,
        ];
        $headers = $this->mergeArgs($headers, $inputs['args'], 'headers');
        return $headers;
    }

    public function request($headers, $args, $url, $posts)
    {
        $contentType = 'application/json;chrset=utf-8';
        $options = Arr::get($args, 'options', []);
        return Http::withOptions($options)->withHeaders($headers)->
        withBody($posts['body'], $contentType)->post($url);
    }

    public function makeURL($input)
    {
        return config('translator.plugs.aliyun.url');
    }

    public function responseTransformation($json, Rep $rep)
    {
        $data = json_decode($json, true);
        $text = Arr::get($data, 'Data.Translated', '');
        $status = true;
        $err_code = Arr::get($data, 'Code');
        if ($err_code != 200) {
            $status = false;
        }
        $rep->set($text, $status, Arr::get($data, 'Message'), $json);
        return $rep;
    }

    protected static function MD5_Base64($s)
    {
        if (empty($s)) {
            return null;
        }
        $md5Hash = md5($s, true);
        $base64Str = base64_encode($md5Hash);
        return $base64Str;
    }

    function HMACSha1($data, $key)
    {
        $rawHmac = hash_hmac('sha1', $data, $key, true);
        $result = base64_encode($rawHmac);
        return $result;
    }

    function toGMTString()
    {
        $df = new \DateTimeZone('GMT');
        $datetime = new \DateTime('now');
        $datetime->setTimezone($df);
        return $datetime->format('D, d M Y H:i:s T');
    }
}
