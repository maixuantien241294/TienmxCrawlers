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
        $check = $this->checkXpath($rule);
        $htmlString = "";
        if ($check === false) {
            $htmlString = $this->parseDom($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock);
        } else {
            $htmlString = $this->parseXpath($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock);
        }
        /**
         * @remove by dom or expath
         */

        if (!empty($valueRemoveXpath)) {

            $checkRemove = $this->checkXpath($valueRemoveXpath);
            if ($checkRemove === false) {
                $htmlString = $this->removeDom($htmlString, $valueRemoveXpath);
            } else {
                $htmlString = $this->removeXpath($htmlString, $valueRemoveXpath);
            }
        }
        if(!empty($valueRemove)){
            $htmlString = $this->removeValue($valueRemove, '', $htmlString);
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
        for ($j = 0; $j < count($tagsSrc); $j++) {
            $html1 = HtmlDomParser::str_get_html($html);
            if ($html1 != false) {
                $listImg = $html1->find('img');
                if (count($listImg) > 0) {
                    foreach ($listImg as $key => $item) {
                        $oldImg = $item->getAttribute($tagsSrc[$j]);

                        if (!empty($oldImg)) {
                            if (!preg_match('/' . $domain . '/', $oldImg, $match)
                                && empty(parse_url($oldImg, PHP_URL_HOST)) && !empty($oldImg)) {
                                $oldImg = $linkWebsite . $oldImg;
                            }
                            $item->setAttribute('src', $oldImg);
                        }
                    }
                }
                $html = $html1->save();
            }
        }
        /*
         * @desc end download ảnh
         */
        return $html;
    }

    public function parseXpath($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock)
    {
        $htmlString = "";
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);

        $ruleParse = $this->getRules($rule);
        $nodelist = $xpath->evaluate($ruleParse);
        foreach ($nodelist as $key => $item) {
            $htmlStringFirst = $html->saveHTML($item);
        }
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        @$domDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $htmlStringFirst);
        $xpathEnd = new \DOMXPath($domDocument);


        /**
         * @desc lấy tất cả các ảnh theo tagsrc
         */
        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $tagsSrc = explode(',', $tagsSrc);
        for ($j = 0; $j < count($tagsSrc); $j++) {
            $listImg = $xpathEnd->query('//img');
            foreach ($listImg as $key => $item) {
                $oldImg = $listImg->item($key)->getAttribute($tagsSrc[$j]);

                if (!empty($oldImg)) {
                    if (!preg_match('/' . $domain . '/', $oldImg, $match)
                        && empty(parse_url($oldImg, PHP_URL_HOST)) && !empty($oldImg)) {
                        $oldImg = $linkWebsite . $oldImg;
                    }
                    $listImg->item($key)->setAttribute('src', $oldImg);
                }
            }
        }
        /*
         * @desc end download ảnh
         */
        $valueBlock = $domDocument->getElementsByTagName($valueRemoveBlock);
        if ($valueBlock->item(0)) {
            for ($i = $valueBlock->length; --$i >= 0;) {
                $href = $valueBlock->item($i);
                $href->parentNode->removeChild($href);
            }
        }

        $htmlString = $domDocument->saveHTML();

        //$htmlString = htmlspecialchars_decode($this->removeValue($valueRemove, '', htmlspecialchars($htmlString)));
        // $trim_off_front = strpos($htmlString, '<body>') + 6;
        // $trim_off_end = (strrpos($htmlString, '</body>')) - strlen($htmlString);
        // $htmlString = substr($htmlString, $trim_off_front, $trim_off_end);
        return $htmlString;
    }
}