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
    public $misdn = 'phone_number';
    public $thong_so_ky_thuat = 'thong_so_ky_thuat';
    public $thong_tin_shop = 'thong_tin_shop';
    public $giao_trinh = 'giao_trinh';
    public $link = 'link';
    public $crawlerUrl = 1;
    public $crawlerGetContent = 2;
    public $crawlerPhantomjs = 4;
    public $crawlerPuppeteer = 3;
    public $crawlerNightmare = 5;
    public $crawlerCasper = 6;
    public $crawlerSelenium = 7;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    public function __construct($wget, $saveFolder, $urlFile)
    {
        $this->wget = $wget;
        $this->saveFolder = $saveFolder;
        $this->urlFile = $urlFile;
    }


    public function getData($contentHtml, $cateId = "", $tagsSrc, $rules, $linkWebsite, $domain, $download, $replaceImg = [], $webRuleImgSpec, $showArray = 0,$linkCrawler = "")
    {

        $linkWebsite = $this->getUrl($linkWebsite);
        $return = ['error' => true, 'message' => "lỗi hệ thống", 'content' => ""];
        try {
            if (empty($contentHtml)) {
                $return['message'] = "Khong co content";
                return $return;
            }
            $temp = [];

            foreach ($rules as $k => $val) {
                $descLink = $linkCrawler;

                $htmlString = "";
                $valueRemove = isset($val['value_remove']) ? $val['value_remove'] : "";
                $valueRemoveXpath = isset($val['value_remove_xpath']) ? $val['value_remove_xpath'] : "";
                $valueRemoveBlock = isset($val['value_remove_block']) ? $val['value_remove_block'] : "";
                switch ($val['type']) {
                    case $this->text:
                        $query = $val['value'];
                        $cText = new CrawlerText();
                        if (!empty($query)) {
                            $htmlString = $cText->executeText($contentHtml, $query, $valueRemove);
                        } else {
                            $htmlString = "";
                        }

                        break;
                    case $this->plaintext:
                        $query = $val['value'];
                        $cText = new CrawlerText();
                        if (!empty($query)) {
                            $htmlString = $cText->executeText($contentHtml, $query, $valueRemove);
                        } else {
                            $htmlString = "";
                        }
                        break;
                    case $this->price:
                        $query = $val['value'];
                        $cPrice = new CrawlerPrice();
                        if (!empty($query)) {
                            $htmlString = $cPrice->executePrice($contentHtml, $query, $valueRemove);
                        } else {
                            $htmlString = "";
                        }

                        break;
                    case $this->html:
                        $query = $val['value'];
                        $cHtml = new CrawlerHtml();
                        if (!empty($query)) {
                            $htmlString = $cHtml->executeHtml($contentHtml, $query, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock, $download,$descLink);
                        } else {
                            $htmlString = "";
                        }

                        break;
                    case $this->src:
                        $cSrc = new CrawlerSrc();
                        if (!empty($val['value'])) {
                            $htmlString = $cSrc->executeSrc($contentHtml, $val['value'], $tagsSrc, $linkWebsite, $domain, $valueRemove, $replaceImg, $webRuleImgSpec, $download);
                        } else {
                            $htmlString = "";
                        }

                        break;
                    case $this->content:
                        $query = $val['value'];
                        $cHtml = new CrawlerHtml();
                        if (!empty($query)) {
                            $htmlString = $cHtml->executeHtml($contentHtml, $query, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock, $download,$descLink);
                        } else {
                            $htmlString = "";
                        }

                        break;
                    case $this->misdn:
                        $query = $val['value'];
                        $htmlString = "";

                        $cMisdn = new CrawlerMisdn();
                        if (!empty($query)) {
                            $htmlString = $cMisdn->executeMisdn($contentHtml, $query, $linkWebsite, $domain);
                        }
                        break;
                    case $this->thong_so_ky_thuat:
                        $cThongSoKyThuat = new CrawlerThongSoKyThuat();

                        $htmlString = $cThongSoKyThuat->executeThongSo($contentHtml, $domain, $val);

                        break;
                    case $this->thong_tin_shop:
                        $query = $val['value'];
                        $htmlString = "";
                        $cThongTinShop = new CrawlerThongTinShop();
                        if (!empty($query)) {
                            $htmlString = $cThongTinShop->executeInfoShop($contentHtml, $query, $linkWebsite, $domain);
                        }
                        break;
                    case  $this->giao_trinh:
                        $cGiaoTrinh = new CrawlerGiaoTrinh();
                        $htmlString = $cGiaoTrinh->executeGiaoTrinh($contentHtml, $domain, $val);
                        break;
                    case $this->link:
                        $query = $val['value'];
                        $cLink = new CrawlerLink();
                        $htmlString = $cLink->executeLink($contentHtml, $domain,$linkWebsite, $query);
                        break;
                }
                $rules[$k]['content'] = $htmlString;
                if ($download == $this->isDownload) {
                    if ($showArray == 0 && in_array($rules[$k]['type'], [$this->src, $this->price])) {
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
            /**
             * @desc Lấy các thông số thẻ meta
             */
            $cMeta = new CrawlerMeta();
            $meta = $cMeta->executeMeta($contentHtml, $linkWebsite, $domain);
            if (!empty($temp)) {

                if (!empty($meta)) {
                    if ($download == $this->isDownload) {
                        $tempMeta = [];
                        foreach ($meta as $val) {
                            $tempMeta[$val['key']] = $val['content'];
                        }
                        $temp = array_merge($temp, $tempMeta);
                    } else {
                        $temp = array_merge($temp, $meta);
                    }

                }
                $return['error'] = false;
                $return['message'] = 'sucess!';
                $return['content'] = $temp;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }

        return $return;
    }


    public function crawlerHtml($data = array(), $server = 1)
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
                    $respone = $this->crawlerByPhantomjs($data);
                    if ($respone['errors'] == true) {
                        $return['message'] = 'Không lấy được dữ liệu';
                        return $return;
                    }
                    $content = $respone['content'];
                    break;
                case $this->crawlerPuppeteer:
                    $respone = $this->crawlerByPuppeteer($data);
                    if ($respone['errors'] == true) {
                        $return['message'] = 'Không lấy được dữ liệu';
                        return $return;
                    }
                    $content = $respone['content'];
                    break;
                case $this->crawlerNightmare:
                    $respone = $this->crawlerByNightmare($data);
                    if ($respone['errors'] == true) {
                        $return['message'] = 'Không lấy được dữ liệu';
                        return $return;
                    }
                    $content = $respone['content'];
                    break;
                case $this->crawlerCasper:
                    $respone = $this->crawlerByCasper($data);
                    if ($respone['errors'] == true) {
                        $return['message'] = 'Không lấy được dữ liệu';
                        return $return;
                    }
                    $content = $respone['content'];
                    break;
                case $this->crawlerSelenium:
                    $respone = $this->crawlerBySelenium($data, $server);
                    if ($respone['errors'] == true) {
                        $return['message'] = 'Không lấy được dữ liệu';
                        return $return;
                    }
                    $content = $respone['content'];
                    break;
            }
//
            if (empty($content)) {
                return $return;
            }
            /**
             * @author lưu file
             */

            $content = str_replace('window.parent != window', 'window.parent == window', $content);
            $link = $this->addJsContent($data, $content);
            $return['message'] = $link;
            $return['error'] = false;
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }


}