<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 7/1/2018
 * Time: 2:30 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerStore
{
    use BaseTrait;

    public function crawler($html,$rule){
        $list = [];
        $dom = HtmlDomParser::str_get_html($html);
        $element = $dom->find($rule);
        if (count($element) > 0) {
            foreach ($element as $item) {
                if(!empty($item->text())){
                    array_push($list,trim($item->text()));
                }
            }
        }
        return $list;
    }
    public function crawlerXpath($contentHtml,$rule){

        $list = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);
//        $rule = $rule . '/text()';
        $nodelist = $xpath->query($rule);
        if ($nodelist->length > 0) {
            foreach ($nodelist as $key => $item) {
                if(!empty(trim($nodelist->item($key)->nodeValue))){
                    array_push($list,trim($nodelist->item($key)->nodeValue));
                }
            }
        }
        return $list;
    }
    public function removeQuote($string)
    {
        $string = trim($string);
        $string = str_replace("\'", "'", $string);
        $string = str_replace("'", "''", $string);

        return $string;
    }
}