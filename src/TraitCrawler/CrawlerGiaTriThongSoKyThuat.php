<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 6/15/2018
 * Time: 3:15 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerGiaTriThongSoKyThuat
{
    use BaseTrait;

    public function crawler($contentHtml, $queryAll)
    {
        $newString = [];
        $ruleHtml = $this->getRuleHtml($queryAll);
        if (!empty($ruleHtml)) {
            for ($i = 0; $i < count($ruleHtml); $i++) {
                $check = $this->checkXpath($ruleHtml[$i]);
                if ($check === false) {
                    $newString = $this->parseDom($contentHtml, $ruleHtml[$i]);

                } else {
                    $newString = $this->parseXpath($contentHtml, $ruleHtml[$i]);
                }
            }
        }
        return $newString;
    }
    public function parseDom($contentHtml,$rule){
        $temp = [];
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        if (count($element) > 0) {
            foreach ($element as $item) {
                if(!empty($item->text())){
                    array_push($temp,$item->text());
                }
            }
        }
        return $temp;
    }
    public function parseXpath($contentHtml, $rule)
    {
//        dd($contentHtml);
        $temp = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);

        $ruleParse = $this->getRules($rule);
        $nodelist = $xpath->query($ruleParse);
        if($nodelist->length > 0){
            for($i=0;$i<$nodelist->length;$i++){
//                dd($nodelist->item(2));
                $value = $nodelist->item($i)->nodeValue;
                if(!empty($value)){
                    array_push($temp,$value);
                }
            }
        }
        return $temp;
    }
}