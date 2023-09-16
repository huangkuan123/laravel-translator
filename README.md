## 这是一个集成了多个翻译平台的 `文本翻译` 组件，具有一定的可扩展性。

<hr>
现有集成平台：<br>

1. 百度翻译开放平台
2. 百度智能机器翻译平台
3. 有道翻译
4. 阿里机器翻译
5. 腾讯机器翻译

注意，这一切都是文本翻译。其中部分平台主推使用他们的 `SDK`，本组件并未使用他们提供的`SDK`，因为这仅是文本翻译的组件。
该包统一使用 `HTTP` 客户端，用`POST`方法进行请求，这也是大多数组件推荐的请求方式。

## 安装方式
1. 下载
```
composer require huangkuan/laravel-translator
```
2. 发布配置文件
```
 php artisan vendor:publish --tag=laravel-translator
 或
 php artisan vendor:publish --provider=Huangkuan\LaravelTranslator\Providers\TranslatorServiceProvider
```

## 使用方式

### 获取实例的几种方式

```php
    use Huangkuan\LaravelTranslator\Translator;
    use Huangkuan\LaravelTranslator\LaravelTranslator;
    //方式 1，使用 translator.php 配置文件中的默认组件(default键对应值)。
    $translator = new Translator();
    //方式 2，使用 translator.php 配置文件中 plugs 键中数组中的键，以切换组件，如 baidu_open
    $translator = new Translator('baidu_open'); 
    //方式 3. 从容器中获取实例
    $translator = app('laravel-translator');//获取到的组件为默认组件
    //方式 4. 使用门面直接调用。
    LaravelTranslator::xxx();
```

### 调用翻译接口

```php
    //以 第一种获取实例为例
    $translator = new Translator();
    //最简使用方式,trans 方法入参顺序，源语言，目标语言，待翻译语句，其他参数(数组).
    $rep = $translator->trans('zh', 'en', '你好世界!',
            [
                'options'=>['verify' => false],
                'posts'=> ['email' => '1@qq.com']
            ]
        );
    //获取响应结果
    $result =  $rep->getResult();
    //获取全部响应
    $msg = $rep->getMsg();
```

```text
$result 结构：
array: 2 [
▼
"status" => true, //是否翻译成功
"text" => "hello world",//翻译结果
]

$msg 结构：
array: 4 [
▼
"http_code" => 200, //http响应码
"err" => "zh-CHS2en", //如果未翻译成功，这个字段为错误信息
"raw" => "{"returnPhrase":["你好世界"],"query":"你好世界","errorCode":"0"...},//原始响应body,每个组件都不一样。
"rep_headers" => array: 14 [▶],//原始响应头信息，每个组件都不一样
]
```

### 动态切换翻译组件

```php
    //默认组件
    $translator = new Translator();
    //切换成有道组件
    $translator->setPlugs(new \Huangkuan\LaravelTranslator\Plugs\Youdao());
    //切换成阿里云组件
    $translator->setPlugs(new \Huangkuan\LaravelTranslator\Plugs\Aliyun());
    //链式调用
     $translator->setPlugs(new \Huangkuan\LaravelTranslator\Plugs\XXX())->trans();
    //门面切换
    \Huangkuan\LaravelTranslator\LaravelTranslator::setPlugs()->trans();
```

## trans 方法的第四个参数

有时候我们会自定义一些数据，进行请求，可能是想往`post`数据中加一些东西，
有时候想往请求头中加一些，有时候是想对`Http`客户端进行一些配置。这个时候我们可以灵活使用
`trans`方法的第四个参数(args)。这个参数是非常有用的。<br>
**这个参数将会覆盖掉原有的默认值**,如下图，某组件默认采用的请求头
格式为：`Content-Type : application/x-www-form-urlencoded`,下面这段代码将会将 `Content-Type` 替换成
`application/json`。

```php
    $rep = $translator->trans('zh', 'en', '你好世界!',
            [
                'options'=>['verify' => false],
                'posts'=> ['email' => '1@qq.com'],
                'headers'=>['Content-Type'=>'application/json']
            ]
        );
    //从上面这个例子中可以看出来
    //1.如果想往请求的post数据中，增加数据，那可以放到posts数组中。
    //2.如果想在 Http 客户端中，增加配置，比如 SSL 验证关闭，可以放到options数组中。
    //3.如果想放在请求头中，则应该放入到 headers 数组中。

    //以阿里组件为例，它可以在 POST 接收一个`Context` 参数，来表示待翻译文本的上下文环境。
    //从而给出更准确的翻译内容，而这个参数是非必填的。这时我们可以使用第四个参数中的posts。
    $translator->setPlugs(new \Huangkuan\LaravelTranslator\Plugs\Aliyun());
    $rep = $translator->trans('zh', 'en', '你好世界!',
            [
                'options'=>['verify' => false],
                'posts'=> ['Context' => '我是上下文环境的文本内容']
            ]
        );
```

## 自定义翻译组件

1. 需继承 `Huangkuan\LaravelTranslator\BaseTranslat` 这个类。
2. 按需重写 `BaseTranslat` 中的方法。可以参考已有组件的写法。
3. 在配置文件  `translator.php` 配置文件中 `plugs` 注册自己的组件。

## 优化建议

1. 可以将多个短文本，以符号分割并连接起来，进行翻译，能够节约网络资源。有些组件是按次收费，有些按翻译字数收费，可根据收费标准进行优化。
2. 如果是短文本，可以将已翻译的文本存储起来，下次调用接口前，先查是否有翻译过这个文本，可以节约网络资源和接口费用。
