<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:04 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerPrice
{
    use BaseTrait;

    public function executePrice($contentHtml, $rule, $valueRemove)
    {
        $htmlString = "";
        //lấy tất cả là số trong chuỗi tring;
        $check = $this->checkXpath($rule);
        if ($check === false) {
            $htmlString = $this->parseDom($contentHtml, $rule, $valueRemove);
        } else {
            $htmlString = $this->parseXpath($contentHtml, $rule, $valueRemove);
        }
        $price = [];
        /**
         * @desc => Kiểm tra nếu có dấu gạch ngang `-`
         */
        $expPrice = explode('-', $htmlString);
        if (count($expPrice) >= 1) {
            foreach ($expPrice as $item) {
                $priceItem = "";
                if (preg_match_all('/\d+/', $item, $matches)) {
                    $matches = $matches[0];
                    foreach ($matches as $value) {
                        $priceItem .= $value;
                    }
                }
                array_push($price, $priceItem);
            }
        }
        return $price;
    }

    protected function parseDom($contentHtml, $rule, $valueRemove)
    {
        $htmlString = "";
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        if (count($element) > 0) {
            foreach ($element as $item) {
                $htmlString = $item->text();
            }
        }
        return $htmlString;
    }

    protected function parseXpath($contentHtml, $rule, $valueRemove)
    {
        $htmlString = "";
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);
        $ruleParse = $this->getRules($rule);
        $ruleParse = $ruleParse . '/text()';
        $nodelist = $xpath->query($ruleParse);
        if ($nodelist->length > 0) {
            $htmlString = trim($nodelist->item(0)->nodeValue);
        }
        return $htmlString;
    }
}