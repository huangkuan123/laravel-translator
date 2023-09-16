<?php
return [
    'default' => 'baidu_open',//默认插件
    'plugs' => [
        'baidu_open' => [//百度翻译开放平台-文档地址：https://api.fanyi.baidu.com/doc/21
            'path' => \Huangkuan\LaravelTranslator\Plugs\Baidu::class,
            'url' => 'http://api.fanyi.baidu.com/api/trans/vip/translate',//http://api.fanyi.baidu.com/api/trans/vip/translate
            'app_id' => '',
            'secret_key' => ''
        ],
        'baidu_bce' => [//百度智能机器翻译-云文本翻译-文档地址：https://ai.baidu.com/ai-doc/MT/4kqryjku9
            'path' => \Huangkuan\LaravelTranslator\Plugs\BaiduBCE::class,
            'url' => 'https://aip.baidubce.com/rpc/2.0/mt/texttrans/v1',
            'app_id' => '',
            'api_key' => '',
            'secret_key' => ''
        ],
        'youdao' => [//文档：https://ai.youdao.com/DOCSIRMA/html/trans/api/wbfy/index.html
            'path' => \Huangkuan\LaravelTranslator\Plugs\Youdao::class,
            'url' => 'https://openapi.youdao.com/api',
            'app_key' => '',
            'secret_key' => ''
        ],
        'tencent' => [//文档：https://cloud.tencent.com/document/product/551/15619
            'path' => \Huangkuan\LaravelTranslator\Plugs\Tencent::class,
            'url' => 'tmt.tencentcloudapi.com',//不要加协议，内部自动转https
            'app_id' => '',
            'secret_id' => '',
            'secret_key' => '',
            'version' => '2018-03-21',//固定值，如官网sdk有变更，才需要变更
            'sdk_version' => 'SDK_PHP_3.0.977',//固定值，如官网sdk有变更，才需要变更
            'action' => 'TextTranslate',//固定值文本翻译，如官网sdk有变更，才需要变更
            'region' => 'ap-shanghai',//必填值，根据需要更换
            'project_id' => 0//必填值，根据需要更换
        ],
        'aliyun' => [//文档：https://help.aliyun.com/document_detail/108551.html?spm=a2c4g.96396.0.0.64076054JtQwIO
            'path' => \Huangkuan\LaravelTranslator\Plugs\Aliyun::class,
            'url' => 'http://mt.cn-hangzhou.aliyuncs.com/api/translate/web/general',//通用版
            'access_key_id' => '',
            'access_secret' => '',
            'x_acs_version' => '2019-01-02',//固定值文本翻译，如官网sdk有变更，才需要变更
            'scene' => 'general',//必填值，通用版
        ],
    ],
];
