<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 3/5/2019
 * Time: 11:18 AM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerDescForKeyword
{
    use BaseTrait;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    public function executeHtml($contentHtml, $rule)
    {
        $htmlString = "";
        try {
            $ruleHtml = $this->getRuleHtml($rule);
            if (!empty($ruleHtml)) {
                for ($i = 0; $i < count($ruleHtml); $i++) {
                    $ruleHtml[$i] = trim($ruleHtml[$i]);
                    if (!empty($ruleHtml[$i])) {
                        $check = $this->checkXpath($ruleHtml[$i]);
                        if ($check === false) {
                            $newString = $this->parseDom($contentHtml, $ruleHtml[$i]);
                        } else {
                            $newString = $this->parseXpath($contentHtml, $ruleHtml[$i]);
                        }
                        $htmlString = $htmlString . $newString;
                    }
                }

            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

        return $htmlString;
    }

    public function parseDom($contentHtml, $rule)
    {
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        $html = "";
        if (count($element) > 0) {

            foreach ($element as $item) {
                $html .= $item->outertext();
            }
            $html = html_entity_decode($html);
        }
        $html1 = HtmlDomParser::str_get_html($html);
        if ($html1 != false) {
            $listHref = $html1->find('a');
            if (count($listHref) > 0) {
                foreach ($listHref as $key => $item) {
                    dd($item->text());
                }
            }
        }
        return $html;
    }

    public function parseXpath($contentHtml, $rule)
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