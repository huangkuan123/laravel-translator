<?php

namespace Huangkuan\LaravelTranslator\Plugs;

use Huangkuan\LaravelTranslator\BaseTranslat;
use Huangkuan\LaravelTranslator\Rep;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Youdao extends BaseTranslat
{
    public function makeURL($inputs)
    {
        return config('translator.plugs.youdao.url');
    }

    public function makePosts($inputs)
    {
        $appkey = config('translator.plugs.youdao.app_key');
        $salt = Str::random(5);
        $q = $inputs['text'];
        $ntime = time();
        $secret_key = config('translator.plugs.youdao.secret_key');
        $posts = [
            'from' => $inputs['from'],
            'to' => $inputs['to'],
            'q' => $q,
            'appKey' => $appkey,
            'salt' => $salt,
            'curtime' => $ntime,
            'signType' => 'v3',
            'sign' => self::sign($appkey, $secret_key, $q, $salt, $ntime)
        ];
        $args_posts = Arr::get($inputs, 'args.posts', []);
        if (!empty($args_posts)) {
            $posts = array_merge($posts, $args_posts);
        }
        return $posts;
    }

    public function makeHeaders($inputs)
    {
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $headers = $this->mergeArgs($headers, $inputs['args'], 'headers');
        return $headers;
    }

    public function request($headers, $args, $url, $posts)
    {
        $contentType = 'application/x-www-form-urlencoded';
        $options = Arr::get($args, 'options', []);
        return Http::withOptions($options)->withHeaders($headers)->
        withBody(http_build_query($posts), $contentType)->post($url);
    }

    public function responseTransformation($json, Rep $rep)
    {
        $data = json_decode($json, true);
        $text = Arr::get($data, 'web.0.value.0', '');
        $status = true;
        $err_code = Arr::get($data, 'errorCode');
        if ($err_code != 0) {
            $status = false;
        }
        $rep->set($text, $status, Arr::get($data, 'l'), $json);
        return $rep;
    }


    protected function sign($appKey, $appSecret, $q, $salt, $curtime)
    {
        $strSrc = $appKey . self::getInput($q) . $salt . $curtime . $appSecret;
        return hash('sha256', $strSrc);
    }

    protected function getInput($q)
    {
        if (empty($q)) {
            return null;
        }
        $len = mb_strlen($q, 'utf-8');
        return $len <= 20 ? $q : (mb_substr($q, 0, 10) . $len . mb_substr($q, $len - 10, $len));
    }
}
