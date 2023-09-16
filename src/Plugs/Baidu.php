<?php

namespace Huangkuan\LaravelTranslator\Plugs;

use Huangkuan\LaravelTranslator\BaiduTrait;
use Huangkuan\LaravelTranslator\BaseTranslat;
use Huangkuan\LaravelTranslator\Rep;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Baidu extends BaseTranslat
{

    public function makePosts($inputs)
    {
        $salt = Str::random(8);
        $appid = config('translator.plugs.baidu_open.app_id');
        $secret_key = config('translator.plugs.baidu_open.secret_key');
        $q = $inputs['text'];
        $sign = md5($appid . $q . $salt . $secret_key);
        $base_body = [
            'q' => $q,
            'from' => $inputs['from'],
            'to' => $inputs['to'],
            'appid' => $appid,
            'salt' => $salt,
            'sign' => $sign
        ];
        $base_body = $this->mergeArgs($base_body, $inputs['args'], 'posts');
        $body = self::convert($base_body);
        return ['body' => $body];
    }

    public function makeURL($inputs)
    {
        return config('translator.plugs.baidu_open.url');
    }

    public function responseTransformation($json,  Rep $rep)
    {
        $data = json_decode($json, true);
        $text = Arr::get($data, 'trans_result.0.dst', '');
        $status = true;
        $err_code = Arr::get($data, 'error_code');
        if (!empty($err_code)) {
            $status = false;
        }
        $rep->set($text, $status, Arr::get($data, 'error_msg'), $json);
        return $rep;
    }

    /**
     * @param $headers
     * @param $args
     * @param $url
     * @param $posts
     * @return Response
     */
    public function request($headers, $args, $url, $posts)
    {
        $contentType = 'application/x-www-form-urlencoded';
        $options = Arr::get($args, 'options', []);
        return Http::withOptions($options)->withHeaders($headers)->withBody($posts['body'], $contentType)->post($url);
    }

    protected function convert($args)
    {
        $data = '';
        foreach ($args as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $data .= $key . '[' . $k . ']=' . rawurlencode($v) . '&';
                }
            } else {
                $data .= "$key=" . rawurlencode($val) . "&";
            }
        }
        return trim($data, "&");
    }
}
