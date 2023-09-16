<?php

namespace Huangkuan\LaravelTranslator\Contracts;

use Huangkuan\LaravelTranslator\Rep;

interface Translat
{

    /**
     * @param $posts
     * @return array
     */
    public function makePosts($posts);

    /**
     * @param $headers
     * @return array
     */
    public function makeHeaders($headers);

    /**
     * @param $inputs
     * @return string
     */
    public function makeURL($inputs);

    /**
     * @param $json
     * @param $http_code
     * @param Rep $rep
     * @return Rep
     */
    public function responseTransformation($json, Rep $rep);
}
