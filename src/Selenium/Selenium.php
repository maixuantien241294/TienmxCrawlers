<?php

namespace Tienmx\Crawler\Selenium;

class Selenium
{
    public $executable;
    public $config;
    public $nodeBinary;
    public $configDefine = 'MqFPJ3HnAV';
    public $executableServer;
    public $executableRequest;

    public function __construct()
    {
        $this->config = [];

        $this->path = 'PATH=$PATH:/usr/local/bin';
        $this->nodePath = 'NODE_PATH=`npm root -g`';
        $this->nodeBinary = 'node';
        $this->executableRequest = __DIR__ . '/js/client_request.js';
        $this->executable = __DIR__ . '/js/index.js';
        $this->executableServer = __DIR__ . '/js/server_index.js';
        $this->executableServerRequest = __DIR__ . '/js/server_request.js';
        ini_set('max_execution_time', 1300);
        set_time_limit(1300);
    }

    /**
     * @nếu là 1 thì chay lưu file và đọc ra nếu là 2 thì đẩy ra file luôn
     * @param array $config
     * @param int $server
     * @return array
     * @throws \Exception
     */
    public function html($config = [], $server = 1)
    {

        if (!isset($config['link'])) {
            throw new \Exception('URL or HTML in configuration required', 400);
        }
        $param = $this->getParams($config);
        $this->config = $this->merge($this->config, $config);
        if ($server == 1) {
            $param = $param . ' path_folder' . $this->configDefine . env('PATH_SAVE_FILE', '/var/www/crawler.muazi.vn/storage/app/');
            $fullCommand = $this->nodeBinary . ' '
                . escapeshellarg($this->executableServer) . ' ' . $param;
        } else {
            $fullCommand = $this->nodeBinary . ' '
                . escapeshellarg($this->executableRequest) . ' ' . $param;
        }

        exec($fullCommand, $output, $returnVal);
        $content = "";
        if ($server == 1) {
            if ($returnVal == 0) {
                if (\Storage::exists('download_file_' . $config['port'] . '.php')) {
                    $content = \Storage::get('download_file_' . $config['port'] . '.php');
                    /**
                     * remove file
                     */
//                    \Storage::put('download_file.php', "");
                }
            }
            $result = [
                'ouput' => $content,
                'returnVal' => $returnVal
            ];
        } else {
            $result = [
                'ouput' => $output,
                'returnVal' => $returnVal
            ];
        }


        return $result;
    }

    public function getParams($config = [])
    {
        $param = "";
        foreach ($config as $key => $item) {
            if (in_array($key, ['dom_click', 'link', 'port','domain'])) {
                if (in_array($key, ['dom_click'])) {
                    if (!empty($config[$key])) {
                        $xpath = "";
                        $explode = explode(',', $config[$key]);
                        for ($i = 0; $i < count($explode); $i++) {
                            $xpath .= $key . $this->configDefine . '"' . $explode[$i] . '" ';
                        }
                        $param .= $xpath;
                    } else {
                        unset($config[$key]);
                    }
                } else {
                    $param .= $key . $this->configDefine . '"' . $item . '" ';
                }
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