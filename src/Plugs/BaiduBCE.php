<?php

namespace Huangkuan\LaravelTranslator\Plugs;

use Huangkuan\LaravelTranslator\BaiduTrait;
use Huangkuan\LaravelTranslator\BaseTranslat;
use Huangkuan\LaravelTranslator\Rep;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BaiduBCE extends BaseTranslat
{

    public function makeURL($inputs)
    {
        return config('translator.plugs.baidu_bce.url') . '?access_token=' . self::getAccessToken($inputs);
    }

    public function makePosts($inputs)
    {
        $posts = [
            'from' => $inputs['from'],
            'to' => $inputs['to'],
            'q' => $inputs['text'],
        ];
        $posts = $this->mergeArgs($posts, $inputs['args'], 'posts');
        return ['body' => json_encode($posts)];
    }

    public function request($headers, $args, $url, $posts)
    {
        $contentType = 'application/json;charset=utf-8';
        $options = Arr::get($args, 'options', []);
        return Http::withOptions($options)->withHeaders($headers)->withBody($posts['body'], $contentType)->post($url);
    }

    public function responseTransformation($json, Rep $rep)
    {
        $data = json_decode($json, true);
        $text = Arr::get($data, 'result.trans_result.0.dst', '');
        $status = true;
        $err_code = Arr::get($data, 'error_code');
        if (!empty($err_code)) {
            $status = false;
        }
        $rep->set($text, $status, Arr::get($data, 'error_msg'), $json);
        return $rep;
    }

    protected function getAccessToken($inputs)
    {
        $postData = [
            'grant_type' => 'client_credentials',
            'client_id' => config('translator.plugs.baidu_bce.api_key'),
            'client_secret' => config('translator.plugs.baidu_bce.secret_key')
        ];
        $url = 'https://aip.baidubce.com/oauth/2.0/token?' . http_build_query($postData);
        $response = Http::withOptions($inputs['args'])->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post($url);
        return json_decode($response->body())->access_token;
    }


}
