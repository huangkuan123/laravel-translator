<?php

namespace Huangkuan\LaravelTranslator;

use Huangkuan\LaravelTranslator\Contracts\Translat;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class Translator
{
    /**
     * @var BaseTranslat
     */
    protected $translat;

    public function __construct($translat = 'default')
    {
        if (empty($translat) || is_string($translat)) {
            $translat_name = $translat;
            if (empty($translat_name)) {
                $translat_name = 'default';
            }
            if ($translat_name == 'default') {
                $translat_name = config('translator.default');
                if (empty($translat_name)) {
                    throw new \Exception('No default plug-in is translator.php');
                }
            }
            $plugs = config('translator.plugs');
            if (!array_key_exists($translat_name, $plugs)) {
                throw new \Exception('The  plug-in ' . $translat_name . ' is not in the plugs array of the configuration file');
            }
            $class_path = Arr::get($plugs, $translat_name . '.path');
            $translat = App::make($class_path);
            self::setPlugs($translat);
            return;
        }
        if (is_object($translat) && $translat instanceof Translat) {
            self::setPlugs($translat);
            return;
        }
        return new \Exception('plug error');
    }

    public function setPlugs(Translat $translat)
    {
        $this->translat = $translat;
        return $this;
    }

    public function trans($from, $to, $text, $args = []): Rep
    {
        $inputs = compact('from', 'to', 'text', 'args');
        $posts = $this->translat->makePosts($inputs);
        $headers = $this->translat->makeHeaders($inputs);
        $url = $this->translat->makeURL($inputs);
        $response = $this->translat->request($headers, $args, $url, $posts);
        $rep = new Rep();
        $rep->setBase($response->headers(), $response->status());
        return $this->translat->responseTransformation($response->body(), $rep);
    }
}
