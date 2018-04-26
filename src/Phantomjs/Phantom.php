<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/24/2018
 * Time: 3:54 PM
 */

namespace Tienmx\Crawler\Phantomjs;
class Phantom
{
    public $executable;
    public $config;
    public $nodeBinary;
    public function __construct()
    {
        $this->config = [];
        // default config
        $this->config['goto']['waitUntil'] = ['load', 'domcontentloaded', 'networkidle0', 'networkidle2'];
        $this->config['viewport']['width'] = 1024;
        $this->config['viewport']['height'] = 800;

        $this->path = 'PATH=$PATH:/usr/local/bin';
        $this->nodePath = 'NODE_PATH=`npm root -g`';
        $this->nodeBinary = 'phantomjs';
//        $this->executable = 'D:\OS\crawler\vendor\tienmx\crawlers\src\Phantomjs/js/index.js';
        $this->executable = __DIR__ . '/js/index.js';
    }

    public function html($config = [])
    {
        if (!isset($config['link'])) {
            throw new \Exception('URL or HTML in configuration required', 400);
        }
        $param = $this->getParams($config);
        $this->config = $this->merge($this->config, $config);

        $fullCommand = $this->nodeBinary . ' '
            . escapeshellarg($this->executable) . ' ' . $param;
        //echo date('d-m-y H:i:s');
//        dd($fullCommand);
        exec($fullCommand, $output, $returnVal);

        $result = [
            'ouput' => $output,
            'returnVal' => $returnVal
        ];
        return $result;
    }

    public function getParams($config = [])
    {
        $param = "";
        foreach ($config as $key => $item) {
            if (in_array($key, ['web_xpath_active', 'web_xpath_active_detail', 'web_xpath_active_cate'])) {
                if (!empty($config[$key])) {
                    $xpath = "";
                    $explode = explode(',', $config[$key]);
                    for ($i = 0; $i < count($explode); $i++) {
                        $xpath .= $key . '=' . $explode[$i] . ' ';
                    }
                    $param .= $xpath;
                } else {
                    unset($config[$key]);
                }
            } else {
                $param .= $key . '=' . $item . ' ';
            }

        }
        return $param;
    }

    private static function merge($a, $b)
    {
        $res = $a;
        foreach ($b as $k => $v) {
            if (is_int($k)) {
                if (array_key_exists($k, $res)) {
                    $res[] = $v;
                } else {
                    $res[$k] = $v;
                }
            } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                $res[$k] = self::merge($res[$k], $v);
            } else {
                $res[$k] = $v;
            }
        }
        return $res;
    }
}