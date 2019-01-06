<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:04 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerHtml
{
    use BaseTrait;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    public  function remove_html($description)
    {
        $description = preg_replace('/(<[^>]+) style.*?=.*?".*?"/i', '$1', $description);
        $description = preg_replace('/(<[^>]+) class.*?=.*?".*?"/i', '$1', $description);
        $description = preg_replace('/(<[^>]+) id.*?=.*?".*?"/i', '$1', $description);
        $description = preg_replace('/(<[^>]+) link.*?=.*?".*?"/i', '$1', $description);
        $description = preg_replace('/(<[^>]+) width.*?=.*?".*?"/i', '$1', $description);
        $description = preg_replace('/(<[^>]+) height.*?=.*?".*?"/i', '$1', $description);
        $description = preg_replace('/(<[^>]+) href.*?=.*?".*?"/i', '$1', $description);
        $description = preg_replace("/<script.*?\/script>/s", "", $description);
        $description = preg_replace("/<iframe.*?\/iframe>/i", "", $description);
        $description = preg_replace("/<input.*?\/>/i", "", $description);
        $description = preg_replace("/<select.*?\/select>/s", "", $description);
        $description = preg_replace("/<textarea.*?\/textarea>/s", "", $description);
        $description = preg_replace("/<form.*?\/form>/s", "", $description);
        $description = preg_replace("/<button.*?\/button>/s", "", $description);
        return $description;
    }
    public function executeHtml($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock, $download,$linkCrawler="")
    {

        if (($domain == 'weshop.com.vn')) {
            $expLink = explode('-', $linkCrawler);
            $endLink = end($expLink);
            $expEndLink = explode('.', $endLink);
            $idWebSite = isset($expEndLink[0]) ? $expEndLink[0] : 0;
            $expLinkDomain = explode('/item/', $linkCrawler);
            $linkDefault = isset($expLinkDomain[0]) ? $expLinkDomain[0] : 'https://weshop.com.vn/ebay';
            $linkDesc = $linkDefault . '/product-description-' . $idWebSite . '.html';
            $dataDesc = [
                'link' => $linkDesc,
                'type' => 1,
                'dom_click' => "",
                'web_num_wait' => 0
            ];
            $rawlerType = new GetCrawlerType(env('WGET_CMD'), env('UPLOAD_FULL_PATH', ''), env('URL_FILE', ''));

            $contentHtml = $rawlerType->crawlerHtml($dataDesc);
            $contentHtml = isset($contentHtml['message']) ? $contentHtml['message'] : "";

        }
        $htmlString = "";
        $linkWebsite = $this->getUrl($linkWebsite);
        try {
            $ruleHtml = $this->getRuleHtml($rule);
            if (!empty($ruleHtml)) {
                for ($i = 0; $i < count($ruleHtml); $i++) {
                    $ruleHtml[$i] = trim($ruleHtml[$i]);
                    if (!empty($ruleHtml[$i])) {
                        $check = $this->checkXpath($ruleHtml[$i]);
                        if ($check === false) {
                            $newString = $this->parseDom($contentHtml, $ruleHtml[$i], $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock);
                        } else {
                            $newString = $this->parseXpath($contentHtml, $ruleHtml[$i], $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock);
                        }
                        /**
                         * @remove by dom or expath
                         */
                        if (!empty($valueRemoveXpath)) {

                            $checkRemove = $this->checkXpath($valueRemoveXpath);
                            if ($checkRemove === false) {
                                $newString = $this->removeDom($newString, $valueRemoveXpath);
                            } else {
                                $newString = $this->removeXpath($newString, $valueRemoveXpath);
                            }
                        }
                        if (!empty($valueRemove)) {
                            $newString = $this->removeValue($valueRemove, '', $newString);
                        }
                        $htmlString = $htmlString . $newString;
                    }
                }

            }
            if (!empty($htmlString)) {
                $htmlString = trim($htmlString);
                if($domain =='shopee.vn'){
                    $htmlString = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "<b>$2</b>", $htmlString);
                }else{
                    $htmlString = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "<span>$2</span>", $htmlString);
                }

                $htmlString = preg_replace('/\r?\n|\r/', '<br/>', $htmlString);
            }
            $htmlString = $this->remove_html($htmlString);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

        return $htmlString;
    }

    protected function removeDom($contentHtml, $valueRemoveXpath)
    {

        $valueXpath = explode(',', $valueRemoveXpath);
        if (!empty($contentHtml)) {
            $html1 = HtmlDomParser::str_get_html($contentHtml);
            for ($i = 0; $i < count($valueXpath); $i++) {
                $query = $html1->find(trim($valueXpath[$i]));
                if (count($query) > 0) {
                    foreach ($query as $value) {
                        $value->outertext = '';
                    }
                }
            }
            $contentHtml = $html1->save();
        }
        return $contentHtml;
    }

    protected function removeXpath($contentHtml, $valueRemoveXpath)
    {
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        @$domDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpathEnd = new \DOMXPath($domDocument);
        /**
         * @desc remove by xpath
         */
        if (!empty($valueRemoveXpath)) {
            $valueXpath = explode(',', $valueRemoveXpath);
            if (!empty($valueXpath)) {
                for ($i = 0; $i < count($valueXpath); $i++) {
                    $nodeValue = $xpathEnd->query($valueXpath[$i]);
                    if ($nodeValue->length > 0) {
                        foreach ($nodeValue as $value) {
                            $value->parentNode->removeChild($value);
                        }
                    }
                }
                $contentHtml = $domDocument->saveHTML();
            }
        }
        return $contentHtml;
    }

    public function parseDom($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock)
    {
        $linkWebsite = $this->getUrl($linkWebsite);
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        $html = "";
        if (count($element) > 0) {

            foreach ($element as $item) {
                $html .= $item->outertext();
            }
            $html = html_entity_decode($html);
        }

        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $tagsSrc = explode(',', $tagsSrc);

        $html1 = HtmlDomParser::str_get_html($html);
        if ($html1 != false) {
            $listImg = $html1->find('img');
            if (count($listImg) > 0) {
                foreach ($listImg as $key => $item) {
                    for ($j = 0; $j < count($tagsSrc); $j++) {
                        if ($tagsSrc[$j] == 'style') {
                            $style = $item->getAttribute('style');
                            $regex = '/(background-image|background):[ ]?url\([\'"]?(.*?\.(?:png|jpg|jpeg|gif))/i';
                            $regex2 = '/background[-image]*:.*[\s]*url\(["|\']+(.*)["|\']+\)/';
                            preg_match($regex, $style, $matches);


                            if (isset($matches) && count($matches) > 0) {
                                $oldImg = isset($matches[2]) ? $matches[2] : "";
                                if (!empty($oldImg)) {
                                    $oldImg = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $oldImg);
                                }
                            } else {
                                preg_match($regex2, $style, $matches2);
                                if (isset($matches2) && count($matches2) > 0) {
                                    $oldImg = isset($matches2[2]) ? $matches2[2] : "";
                                    if (!empty($oldImg)) {
                                        $oldImg = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $oldImg);
                                    }
                                }
                            }
                        } else {
                            $oldImg = $item->getAttribute($tagsSrc[$j]);
                        }
//                        $oldImg = $item->getAttribute(trim($tagsSrc[$j]));
                        if (!empty($oldImg)) {
                            $newImg = $this->__check_url($oldImg, $domain, $linkWebsite);

                            if (!empty($newImg)) {
                                $item->setAttribute('src', $newImg);
                            }
                        }
                    }
                }
            }
            $html = $html1->save();
        }

        /*
         * @desc end download ảnh
         */
        return $html;
    }

    public function parseXpath($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock)
    {
        try {
            $htmlString = "";
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
            $xpath = new \DOMXPath($html);

            $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
            $tagsSrc = explode(',', $tagsSrc);
            if (!empty($tagsSrc)) {
                for ($s = 0; $s < count($tagsSrc); $s++) {
                    if (isset($tagsSrc[$s]) && $tagsSrc[$s] == 'style') {
                        unset($tagsSrc[$s]);
                    }
                }
            }
            $nodelist = $xpath->evaluate($rule);
            if ($nodelist->length > 0) {
                foreach ($nodelist as $key => $item) {
                    $htmlStringFirst = $html->saveHTML($item);
                }
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                @$domDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $htmlStringFirst);
                $xpathEnd = new \DOMXPath($domDocument);
                $listImg = $xpathEnd->query('//img');
                if (intval($listImg->length) > 0) {
                    foreach ($listImg as $key => $item) {
                        for ($j = 0; $j < count($tagsSrc); $j++) {
                            $oldImg = $listImg->item($key)->getAttribute(trim($tagsSrc[$j]));
                            if (!empty($oldImg)) {
                                $newImg = $this->__check_url($oldImg, $domain, $linkWebsite);
                                if (!empty($newImg)) {
                                    $listImg->item($key)->setAttribute('src', $newImg);
                                }
                            }
                        }
                    }
                }
                $htmlString = $domDocument->saveHTML();
            }
            return $htmlString;

        } catch (\Exception $exception) {

        }
        return $htmlString;
    }
}