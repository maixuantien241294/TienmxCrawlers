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

    public function executeHtml($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock, $download)
    {
        $htmlString = "";
        $ruleHtml = $this->getRuleHtml($rule);
        if (!empty($ruleHtml)) {
            for ($i = 0; $i < count($ruleHtml); $i++) {
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

        return $htmlString;
    }

    protected function removeDom($contentHtml, $valueRemoveXpath)
    {
        $valueXpath = explode(',', $valueRemoveXpath);
        $html1 = HtmlDomParser::str_get_html($contentHtml);
        for ($i = 0; $i < count($valueXpath); $i++) {
            $query = $html1->find($valueXpath[$i]);
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $value->outertext = '';
                }
            }
        }
        $contentHtml = $html1->save();
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
        // $rule = 'div#top-banner-and-menu  div[class=container]  div[class=row single-product]  div[class=col-xs-12 col-sm-12 col-md-9 homebanner-holder]  div[class=detail-block]  div[class=row]  div[class=col-sm-6]  div  div  p  span  span  span';
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
                        $oldImg = $item->getAttribute($tagsSrc[$j]);
                        if (!empty($oldImg)) {
                            $newImg = "";
                            if ($tagsSrc[$j] == 'style') {
                                $regex = '/(background-image|background):[ ]?url\([\'"]?(.*?\.(?:png|jpg|jpeg|gif))/i';
                                preg_match($regex, $image, $matches);
                                if (isset($matches) && count($matches) > 0) {
                                    $image = isset($matches[2]) ? $matches[2] : "";
                                    if (!empty($image)) {
                                        $newImg = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $image);
                                    }
                                }
                            } else {
                                $newImg = $oldImg;
                                if (!preg_match('/' . $domain . '/', $oldImg, $match)
                                    && empty(parse_url($oldImg, PHP_URL_HOST)) && !empty($oldImg)) {
                                    $newImg = $linkWebsite . $oldImg;
                                }
                            }
                            if (!empty($newImg)) {
                                $item->setAttribute('src', $newImg);
//                                $listImg->item($key)->setAttribute('src', $oldImg);
                            }
                        }
//                        if (!empty($oldImg)) {
//                            if (!preg_match('/' . $domain . '/', $oldImg, $match)
//                                && empty(parse_url($oldImg, PHP_URL_HOST)) && !empty($oldImg)) {
//                                $oldImg = $linkWebsite . $oldImg;
//                                if ($this->validImage($oldImg)) {
//                                    $item->setAttribute('src', $oldImg);
//                                }
//                            }
//                        }
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
                            $oldImg = $listImg->item($key)->getAttribute($tagsSrc[$j]);
                            if (!empty($oldImg)) {
                                $newImg = "";
                                if ($tagsSrc[$j] == 'style') {
                                    $regex = '/(background-image|background):[ ]?url\([\'"]?(.*?\.(?:png|jpg|jpeg|gif))/i';
                                    preg_match($regex, $image, $matches);
                                    if (isset($matches) && count($matches) > 0) {
                                        $image = isset($matches[2]) ? $matches[2] : "";
                                        if (!empty($image)) {
                                            $newImg = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $image);
                                        }
                                    }
                                } else {
                                    $newImg = $oldImg;
                                    if (!preg_match('/' . $domain . '/', $oldImg, $match)
                                        && empty(parse_url($oldImg, PHP_URL_HOST)) && !empty($oldImg)) {
                                        $newImg = $linkWebsite . $oldImg;
                                    }
                                }
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