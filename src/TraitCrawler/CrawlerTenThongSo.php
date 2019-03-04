<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 6/15/2018
 * Time: 3:08 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerTenThongSo
{
    use BaseTrait;
    public $regexReplace = ['*', '-', '_', '#', ':', '.'];

    public function crawler($contentHtml, $domain, $queryAll)
    {
        $newString = [];
        $ruleHtml = $this->getRuleHtml($queryAll);
        if (!empty($ruleHtml)) {
            for ($i = 0; $i < count($ruleHtml); $i++) {
                $check = $this->checkXpath($ruleHtml[$i]);
                if ($check === false) {
                    $newString = $this->parseDom($contentHtml,$domain, $ruleHtml[$i]);
                } else {
                    $newString = $this->parseXpath($contentHtml,$domain, $ruleHtml[$i]);
                }
            }
        }
        return $newString;
    }

    public function parseDom($contentHtml, $domain, $rule)
    {
        $temp = [];
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        if (count($element) > 0) {
            foreach ($element as $item) {
                if (!empty($item->text())) {
                    $text = preg_replace('/\s\s+/', ' ', trim($item->text()));
                    $text = str_replace($this->regexReplace, ' ', $text);
                    $text = mb_strtolower(trim($text), 'UTF-8');
                    array_push($temp, $text);
                }
            }
        }
        return $temp;
    }

    public function parseXpath($contentHtml, $domain, $rule)
    {
        $temp = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);

        $ruleParse = $this->getRules($rule);
//        $ruleParse = $ruleParse . '/text()';
        $nodelist = $xpath->query($ruleParse);
        if ($nodelist->length > 0) {
            for ($i = 0; $i < $nodelist->length; $i++) {
                $value = $nodelist->item($i)->nodeValue;
//                if ($domain == 'hc.com.vn') {
//                    $value = utf8_decode($value);
//                }
                if (!empty($value)) {
                    array_push($temp, trim($value));
                }
            }
        }
        return $temp;
    }
}