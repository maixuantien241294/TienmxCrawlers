<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 10:56 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


use Tienmx\Crawler\Casperjs\Casper;
use Tienmx\Crawler\Nightmare\Nightmare;
use Tienmx\Crawler\Phantomjs\Phantom;
use Tienmx\Crawler\Puppeteer\Puppeteer;
use Tienmx\Crawler\Selenium\Selenium;

trait CrawlerTypeTrait
{
    protected $listUserAgents = array(

        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6)    Gecko/20070725 Firefox/2.0.0.6",

        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",

        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",

        "Opera/9.20 (Windows NT 6.0; U; en)",

        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 8.50",

        "Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.1) Opera 7.02 [en]",

        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; fr; rv:1.7) Gecko/20040624 Firefox/0.9",

        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48"

    );

    protected $listHeader = [
        'Connection: keep-alive',
        'Keep-Alive: 300',
        "User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0.1",
        "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
        "Accept-Language: vi,vi;q=0.5"
    ];


    public function getCrawlerGetContent($data = array())
    {
        $url = isset($data['link']) ? $data['link'] : "";
        $content = file_get_contents($url);
        return $content;
    }

    public function getCrawlerCurl($data = array())
    {
        $url = isset($data['link']) ? $data['link'] : "";
        $content = @$this->__getContent($url, $data);
        return isset($content['content']) ? $content['content'] : "";
    }

    public function crawlerByPuppeteer($data = array())
    {
        $return = ['errors' => true, 'msg' => "", 'content' => ''];
        try {
            if (!isset($data['link'])) {
                throw new \Exception('URL or HTML in configuration required', 400);
            }

            $browser = new Puppeteer();
            $browser->isDebug = true;
            $result = $browser->html($data);
            if ($result['returnVal'] === 0) {
                $content = $result['ouput'];
                $content = implode('', $content);
                $return['content'] = $content;
                $return['errors'] = false;
            }
        } catch (\Exception $exception) {
            $return['msg'] = $exception->getMessage();
        }
        return $return;

    }

    public function crawlerByPhantomjs($data = array())
    {
        $return = ['errors' => true, 'msg' => "", 'content' => ''];
        try {
            if (!isset($data['link'])) {
                throw new \Exception('URL or HTML in configuration required', 400);
            }

            $browser = new Phantom();
            $result = $browser->html($data);
            if ($result['returnVal'] === 0) {
                $content = $result['ouput'];
                $content = implode('', $content);
                $return['content'] = $content;
                $return['errors'] = false;
            }
        } catch (\Exception $exception) {
            $return['msg'] = $exception->getMessage();
        }
        return $return;
    }

    public function crawlerByNightmare($data = array()){
        $return = ['errors' => true, 'msg' => "", 'content' => ''];
        try {
            if (!isset($data['link'])) {
                throw new \Exception('URL or HTML in configuration required', 400);
            }

            $browser = new Nightmare();
            $result = $browser->html($data);
            if ($result['returnVal'] === 0) {
                $content = $result['ouput'];
                $content = implode('', $content);
                $return['content'] = $content;
                $return['errors'] = false;
            }
        } catch (\Exception $exception) {
            $return['msg'] = $exception->getMessage();
        }
        return $return;
    }

    public function crawlerByCasper($data=array()){
        $return = ['errors' => true, 'msg' => "", 'content' => ''];
        try {
            if (!isset($data['link'])) {
                throw new \Exception('URL or HTML in configuration required', 400);
            }

            $browser = new Casper();
            $result = $browser->html($data);
            if ($result['returnVal'] === 0) {
                $content = $result['ouput'];
                $content = implode('', $content);
                $return['content'] = $content;
                $return['errors'] = false;
            }
        } catch (\Exception $exception) {
            $return['msg'] = $exception->getMessage();
        }
        return $return;
    }


    public function crawlerBySelenium($data=array()){
        $return = ['errors' => true, 'msg' => "", 'content' => ''];
        try {
            if (!isset($data['link'])) {
                throw new \Exception('URL or HTML in configuration required', 400);
            }

            $browser = new Selenium();
            $result = $browser->html($data);
            if ($result['returnVal'] === 0) {
                $content = $result['ouput'];
                $content = implode('', $content);
                $return['content'] = $content;
                $return['errors'] = false;
            }
        } catch (\Exception $exception) {
            $return['msg'] = $exception->getMessage();
        }
        return $return;
    }

    protected function __getContent($url, $data = array())
    {
        $userAgents = $this->listUserAgents;
        $head = $this->listHeader;

        $newHeader = isset($data['web_header']) ? $data['web_header'] : "";
        $newUserAgents = isset($data['web_user_agents']) ? $data['web_user_agents'] : "";
        /**
         * @desc Thêm params in trong header
         */
        if (!empty($newHeader)) {
            $newExplodeHead = explode('|', $newHeader);
            $head = array_merge($head, $newExplodeHead);
        }
        $head = array_unique($head);
        /**
         * @desc Thêm params trong userAgents
         */
        if (!empty($newUserAgents)) {
            $newExplodeUserAgents = explode('|', $newUserAgents);
            $userAgents = array_merge($userAgents, $newExplodeUserAgents);
        }
        $userAgents = array_unique($userAgents);

        $random = rand(0, count($userAgents) - 1);

        $options = array(

            CURLOPT_CUSTOMREQUEST => "GET",        //set request type post or get
//            CURLOPT_POST => false,        //set to GET
            CURLOPT_USERAGENT => $userAgents[$random], //set user agent
            //CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
            //CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => 0,    // don't return headers
            CURLOPT_HTTPHEADER => $head,
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
//            CURLOPT_PROXY => "127.0.0.1",
//            CURLOPT_PROXYPORT => 80,
            CURLOPT_ENCODING => "",       // handle all encodings
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,      // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        );
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);
        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;
        return $header;
    }
}