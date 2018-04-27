<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/12/2018
 * Time: 3:23 PM
 */

namespace Tienmx\Crawler\Puppeteer;

use Its404\PhpPuppeteer\Browser;

class Puppeteer extends Browser
{
    private $config;
    public $pageClickConfigJs;
    public $pageCrollConfigJs;

    public function __construct()
    {

        $this->executablehtml = __DIR__ . '/js/puppeteer-html.js';
        $this->pageClickConfigJs = __DIR__ . '/js/nhatcuong.js';
        $this->pageCrollConfigJs = __DIR__ . '/js/scrape-infinite-scroll.js';
        $this->isDebug = false;
        parent::__construct();
    }

    public function html($config = [])
    {
        if (!isset($config['link'])) {
            throw new \Exception('URL or HTML in configuration required', 400);
        }
        $param = $this->getParams($config);
        $this->config = $this->merge($this->config, $config);

        $fullCommand = $this->nodeBinary . ' '
            . escapeshellarg($this->executablehtml) . ' ' . $param;
        if ($this->isDebug) {
        }
        $startTime = date('d-m-y H:i:s');
//        exec('casperjs "D:\OS\caspers\index.js" --link=https://www.dienthoaididong.com/ --link_website=https://www.chotot.com/ --domain=chotot.com --tagsSrc= --type=3', $output, $returnVal);
        exec($fullCommand, $output, $returnVal);
        $endTime = date('d-m-y H:i:s');
        $result = [
            'ouput' => $output,
            'returnVal' => $returnVal,
            'startTime' => $startTime,
            'endTime'   =>  $endTime
        ];
        return $result;
    }

    public function pageClick($config = [])
    {
        if (!isset($config['link'])) {
            throw new \Exception('URL or HTML in configuration required', 400);
        }

        $this->config = $this->merge($this->config, $config);
        $param = $this->getParams($config);
        $fullCommand = $this->nodeBinary . ' '
            . escapeshellarg($this->pageClickConfigJs) . ' ' . $param;
//        dd($fullCommand);
        exec($fullCommand, $output, $returnVal);
        $result = [
            'ouput' => $output,
            'returnVal' => $returnVal
        ];
        return $result;
    }

    public function pageCroll($config = [])
    {
        if (!isset($config['link'])) {
            throw new \Exception('URL or HTML in configuration required', 400);
        }

        $this->config = $this->merge($this->config, $config);
        $param = $this->getParams($config);
        $fullCommand = $this->nodeBinary . ' '
            . escapeshellarg($this->pageCrollConfigJs) . ' ' . $param;

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
                        $xpath .= '--' . $key . '=' . $explode[$i] . ' ';
                    }
                    $param .= $xpath;
                } else {
                    unset($config[$key]);
                }
            } else {
                $param .= '--' . $key . '=' . $item . ' ';
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