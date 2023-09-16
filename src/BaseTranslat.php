<?php

namespace Huangkuan\LaravelTranslator;


use Huangkuan\LaravelTranslator\Contracts\Translat;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BaseTranslat implements Translat
{
    /**
     * @param $headers
     * @param $args
     * @param $url
     * @param $posts
     * @return Response
     */
    public function request($headers, $args, $url, $posts)
    {
        $options = Arr::get($args, 'options', []);
        return Http::withHeaders($headers)->withOptions($options)->post($url, $posts);
    }

    /**
     * @param $inputs
     * @return array
     */
    public function makePosts($inputs)
    {
        return Arr::get($inputs, 'posts', []);
    }

    /**
     * @param $inputs
     * @return array
     */
    public function makeHeaders($inputs)
    {
        return Arr::get($inputs, 'headers', []);
    }

    /**
     * @param $inputs
     * @return string
     */
    public function makeURL($inputs)
    {
        return '';
    }

    /**
     * @param $json
     * @param $http_code
     * @param Rep $rep
     * @return Rep
     */
    public function responseTransformation($json, Rep $rep)
    {
        return $rep;
    }

    protected function mergeArgs($data, $args, $key)
    {
        $args_posts = Arr::get($args, $key, []);
        if (!empty($args_posts)) {
            $data = array_merge($data, $args_posts);
        }
        return $data;
    }

}
