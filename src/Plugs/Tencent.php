<?php

namespace Huangkuan\LaravelTranslator\Plugs;

use Huangkuan\LaravelTranslator\BaseTranslat;
use Huangkuan\LaravelTranslator\Rep;
use Illuminate\Support\Arr;

class Tencent extends BaseTranslat
{

    public $posts = '';

    public function makePosts($inputs)
    {
        $posts = [];
        $posts['SourceText'] = $inputs['text'];
        $posts['Source'] = $inputs['from'];
        $posts['Target'] = $inputs['to'];
        $posts['ProjectId'] = config('translator.plugs.tencent.project_id');
        $body = $this->mergeArgs($posts, $inputs['args'], 'posts');
        $posts = json_encode($body);
        $this->posts = $posts;
        return $body;
    }

    public function makeHeaders($inputs)
    {
        $ntime = time();
        $url = config('translator.plugs.tencent.url');
        $content_type = 'application/json';
        $authorization = self::getAuthSign($ntime, $url, $content_type);
        $headers = [
            'Host' => $url,
            'X-TC-Action' => config('translator.plugs.tencent.action'),
            'X-TC-RequestClient' => config('translator.plugs.tencent.sdk_version'),
            'X-TC-Timestamp' => $ntime,
            'X-TC-Version' => config('translator.plugs.tencent.version'),
            'X-TC-Region' => config('translator.plugs.tencent.region'),
            'Content-Type' => $content_type,
            'Authorization' => $authorization
        ];
        $headers = $this->mergeArgs($headers, $inputs['args'], 'headers');
        return $headers;
    }

    public function makeURL($inputs)
    {
        return 'https://' . config('translator.plugs.tencent.url');
    }

    public function responseTransformation($json, Rep $rep)
    {
        $data = json_decode($json, true);
        $text = Arr::get($data, 'Response.TargetText', '');
        $status = true;
        $err_code = Arr::get($data, 'error_code');
        if (!empty($err_code)) {
            $status = false;
        }
        $rep->set($text, $status, Arr::get($data, 'error_msg'), $json);
        return $rep;
    }

    protected function getAuthSign($ntime, $url, $content_type)
    {
        $reqmethod = 'POST';
        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = 'content-type:' . $content_type . "\n" .
            'host:' . $url . "\n";

        $signedHeaders = 'content-type;host';
        $payloadHash = hash('SHA256', $this->posts);
        $canonicalRequest = $reqmethod . "\n" .
            $canonicalUri . "\n" .
            $canonicalQueryString . "\n" .
            $canonicalHeaders . "\n" .
            $signedHeaders . "\n" .
            $payloadHash;

        $algo = 'TC3-HMAC-SHA256';
        $date = gmdate('Y-m-d', $ntime);
        $service = explode(".", $url)[0];
        $credentialScope = $date . '/' . $service . '/tc3_request';
        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        $str2sign = $algo . "\n" .
            $ntime . "\n" .
            $credentialScope . "\n" .
            $hashedCanonicalRequest;
        $skey = config('translator.plugs.tencent.secret_key');
        $signature = self::signTC3($skey, $date, $service, $str2sign);
        $sid = config('translator.plugs.tencent.secret_id');
        $auth = $algo .
            ' Credential=' . $sid . '/' . $credentialScope .
            ', SignedHeaders=content-type;host, Signature=' . $signature;
        return $auth;
    }

    protected function signTC3($skey, $date, $service, $str2sign)
    {
        $dateKey = hash_hmac('SHA256', $date, 'TC3' . $skey, true);
        $serviceKey = hash_hmac('SHA256', $service, $dateKey, true);
        $reqKey = hash_hmac('SHA256', 'tc3_request', $serviceKey, true);
        return hash_hmac('SHA256', $str2sign, $reqKey);
    }

}
