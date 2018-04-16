<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:00 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

class GetCrawlerType
{
    use BaseTrait, CrawlerTypeTrait;

    public $text = 'text';
    public $html = 'html';
    public $price = 'price';
    public $plaintext = 'plaintext';
    public $content = 'content';
    public $src = 'src';
    public $crawlerUrl = 1;
    public $crawlerGetContent = 2;
    public $crawlerPhantomjs = 3;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    public function __construct($wget, $saveFolder, $urlFile)
    {
        $this->wget = $wget;
        $this->saveFolder = $saveFolder;
        $this->urlFile = $urlFile;
    }


    public function getData($contentHtml, $cateId = "", $tagsSrc, $rules, $linkWebsite, $domain, $download)
    {
        $return = ['error' => true, 'message' => "lỗi hệ thống", 'content' => ""];
        try {
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
            $xpath = new \DOMXPath($html);
            $temp = [];
            foreach ($rules as $k => $val) {
                $htmlString = "";
                $valueRemove = $val['value_remove'];
                $valueRemoveXpath = $val['value_remove_xpath'];
                $valueRemoveBlock = $val['value_remove_block'];
                switch ($val['type']) {
                    case $this->text:
                        $query = $val['value'] . '/text()';
                        $cText = new CrawlerText();
                        $htmlString = $cText->executeText($xpath, $query, $valueRemove);
                        break;
                    case $this->plaintext:
                        $query = $val['value'] . '/text()';
                        $cText = new CrawlerText();
                        $htmlString = $cText->executeText($xpath, $query, $valueRemove);
                        break;
                    case $this->price:
                        $query = $val['value'] . '/text()';
                        $cPrice = new CrawlerPrice();
                        $htmlString = $cPrice->executePrice($xpath, $query, $valueRemove);
                        break;
                    case $this->html:
                        $query = $val['value'];
                        $cHtml = new CrawlerHtml();
                        $htmlString = $cHtml->executeHtml($html, $xpath, $query, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock, $download);
                        break;
                    case $this->src:
                        $cSrc = new CrawlerSrc();
                        $htmlString = $cSrc->executeSrc($xpath, $val['value'], $tagsSrc, $linkWebsite, $domain, $valueRemove, $download);
                        break;
                    case $this->content:
                        $query = $val['value'];
                        $cHtml = new CrawlerHtml();
                        $htmlString = $cHtml->executeHtml($html, $xpath, $query, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock, $download);
                        break;
                }
                $rules[$k]['content'] = $htmlString;
                if ($download == $this->isDownload) {
                    if (in_array($rules[$k]['type'], [$this->src, $this->price])) {
                        $temp[$rules[$k]['key']] = json_encode($htmlString);
                    } else {
                        $temp[$rules[$k]['key']] = $htmlString;
                    }
                    if (!empty($cateId)) {
                        $temp['cate_id'] = intval($cateId);
                    }
                } else {
                    $temp[$k] = $rules[$k];
                }

            }
            if (!empty($temp)) {
                $return['error'] = false;
                $return['message'] = 'sucess!';
                $return['content'] = $temp;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }


    public function crawlerHtml($data = array())
    {

        $type = isset($data['type']) ? $data['type'] : $this->crawlerUrl;

        $return = ['success' => 0, 'message' => __('app.error_ajax'), 'content' => '', 'error' => true];
        try {
            $content = "";
            switch ($type) {
                case $this->crawlerUrl:
                    $content = $this->getCrawlerCurl($data);
                    break;
                case $this->crawlerGetContent:
                    $content = $this->getCrawlerGetContent($data);
                    break;
                case  $this->crawlerPhantomjs:
                    $respone = $this->crawlerByPhatom($data);
                    if ($respone['errors'] == true) {
                        $return['message'] = 'Không lấy được dữ liệu';
                        return $return;
                    }
                    $content = $respone['content'];
            }
            if (empty($content)) {
                return $return;
            }
            /**
             * @author lưu file
             */
            $link = $this->addJsContent($data, $content);
            $return['message'] = $link;
            $return['error'] = false;
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }


}