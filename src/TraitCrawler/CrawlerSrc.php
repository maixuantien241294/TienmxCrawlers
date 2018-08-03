<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:05 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerSrc
{
    use BaseTrait;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    /**
     * @param $xpath
     * @param $rule
     * @param $tagsSrc
     * @param $linkWebsite
     * @param $domain
     * @param $valueRemove
     * @return array
     */
    public function executeSrc($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $replaceImg = [], $download)
    {

        $htmlString = [];
        try {
            $ruleHtml = $this->getRuleHtml($rule);
            \Log::info(json_encode($ruleHtml), ['Luat_cate' => 'Luat_cate']);
            if (!empty($ruleHtml)) {
                for ($i = 0; $i < count($ruleHtml); $i++) {
                    $check = $this->checkXpath($ruleHtml[$i]);
                    if ($check === false) {
                        $listImage = $this->parseDom($contentHtml, $ruleHtml[$i], $tagsSrc, $linkWebsite, $domain, $valueRemove, $download);
                    } else {
                        $listImage = $this->parseXpath($contentHtml, $ruleHtml[$i], $tagsSrc, $linkWebsite, $domain, $valueRemove, $download);
                    }
                    if (!empty($listImage)) {
                        foreach ($listImage as $item) {
                            array_push($htmlString, $item);
                        }
                    }

                }
            }
            if ($domain = 'dienmayxanh.com') {
                $listKichThuoc = ['-180x120', '-300x300', '-480x480'];

                $imgReplace = [];
                foreach ($htmlString as $item) {
                    $expImg = [];
                    for ($i = 0; $i < count($listKichThuoc); $i++) {
                        $exp = explode($listKichThuoc[$i], $item);
                        if (count($exp) > 1) {
                            $expImg = $exp;
                        }
                    }
                    $newImg = "";
                    if (count($expImg) > 1) {
                        $enDot = explode('.', $expImg[1]);
                        if (count($enDot) > 0) {
                            $newImg = $expImg[0] . '.' . $enDot[1];
                        } else {
                            $newImg = $expImg[0] . '.jpg';
                        }
                    }
                    if (!empty($newImg)) {
                        array_push($imgReplace, $newImg);
                    }
                }
                if(!empty($imgReplace)){
                    $htmlString = $imgReplace;
                }
            }
            if (!empty($replaceImg) && !empty($htmlString)) {
                $listSearch = [];
                $listReplcae = [];
                for ($i = 0; $i < count($replaceImg); $i++) {
                    if (isset($replaceImg[$i]['key_search']) && !empty($replaceImg[$i]['key_search'])) {
                        array_push($listSearch, $replaceImg[$i]['key_search']);
                        array_push($listReplcae, $replaceImg[$i]['key_replace']);
                    }

                }
                $listNewImg = [];
                foreach ($htmlString as $item) {

                    $newImg = str_replace($listSearch, $listReplcae, $item);
                    if (!empty($newImg)) {
                        array_push($listNewImg, $newImg);
                    }
                }
                if (!empty($listNewImg)) {
                    $htmlString = $listNewImg;
                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        return $htmlString;

    }

    protected function parseDom($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download)
    {
        $htmlString = [];
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $tagsSrc = explode(',', $tagsSrc);

        $explodeLink = explode('/', $linkWebsite);
        if (count($explodeLink) === 4) {
            $linkWebsite = substr($linkWebsite, 0, strlen($linkWebsite) - 1);
        }
        $linkWebsite = $this->getUrl($linkWebsite);

        if (count($element)) {
            foreach ($element as $item) {
                for ($i = 0; $i < count($tagsSrc); $i++) {
                    /**
                     * @tienmx
                     * @date 25/05/2018
                     * @mô tả : Xóa khoảng trắng
                     */
                    $tagsSrc[$i] = trim($tagsSrc[$i]);

                    if ($tagsSrc[$i] == 'style') {
                        $style = $item->getAttribute('style');
                        $regex = '/(background-image|background):[ ]?url\([\'"]?(.*?\.(?:png|jpg|jpeg|gif))/i';
                        $regex2 = '/background[-image]*:.*[\s]*url\(["|\']+(.*)["|\']+\)/';
                        preg_match($regex, $style, $matches);


                        if (isset($matches) && count($matches) > 0) {
                            $image = isset($matches[2]) ? $matches[2] : "";
                            if (!empty($image)) {
                                $image = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $image);
                            }
                        } else {
                            preg_match($regex2, $style, $matches2);
                            if (isset($matches2) && count($matches2) > 0) {
                                $image = isset($matches2[2]) ? $matches2[2] : "";
                                if (!empty($image)) {
                                    $image = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $image);
                                }
                            }
                        }
                    } else {
                        $image = $item->getAttribute($tagsSrc[$i]);
                    }
                    if (!empty($image)) {

                        $image = $this->__check_url($image, $domain, $linkWebsite);
                        array_push($htmlString, $image);
                    }
                }
            }
        }
        return $htmlString;
    }

    protected function parseXpath($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download)
    {
        /**
         * @remove '/' cuối của $linkWebsite
         */
        $htmlString = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);

        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $explodeLink = explode('/', $linkWebsite);
        if (count($explodeLink) === 4) {
            $linkWebsite = substr($linkWebsite, 0, strlen($linkWebsite) - 1);
        }
        $linkWebsite = $this->getUrl($linkWebsite);
        $ruleParse = $this->getRules($rule);
        $tagsSrc = explode(',', $tagsSrc);
        foreach ($tagsSrc as $item) {
            /**
             * @tienmx
             * @date 25/05/2018
             * @mô tả : Xóa khoảng trắng
             */
            $item = trim($item);
            $ruleImg = $ruleParse . '/@' . $item;
            $nodelist = $xpath->query($ruleImg);
            if ($nodelist->length > 0) {
                foreach ($nodelist as $key => $node) {
                    $image = $nodelist->item($key)->value;
                    if ($item == 'style') {

                        $regex = '/(background-image|background):[ ]?url\([\'"]?(.*?\.(?:png|jpg|jpeg|gif))/i';
                        $regex2 = '/background[-image]*:.*[\s]*url\(["|\']+(.*)["|\']+\)/';
                        preg_match($regex, $image, $matches);
                        if (isset($matches) && count($matches) > 0) {
                            $image = isset($matches[2]) ? $matches[2] : "";
                            if (!empty($image)) {
                                $image = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $image);
                            }
                        } else {
                            preg_match($regex2, $image, $matches2);

                            if (isset($matches2) && count($matches2) > 0) {
                                $image = isset($matches2[1]) ? $matches2[1] : "";
                                if (!empty($image)) {
                                    $image = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $image);
                                }
                            }
                        }
                    }

                    $image = $this->__check_url($image, $domain, $linkWebsite);
                    array_push($htmlString, $image);
                }
                break;
            }
        }
        return $htmlString;
    }
}