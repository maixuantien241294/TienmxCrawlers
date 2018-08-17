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

    public function crawler($contentHtml,$domain, $queryAll)
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
    public function parseDom($contentHtml,$domain,$rule){
        ini_set('default_charset', 'utf-8');
        $temp = [];
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        if (count($element) > 0) {
            foreach ($element as $item) {
                if(!empty($item->text())){
                    $text = $item->text();
//                    if ($domain == 'hc.com.vn') {
//                        $text = utf8_decode($item->text());
//
//                    }
                    array_push($temp,$this->removeQuote(trim($text)));
                }
            }
        }
        return $temp;
    }
    protected function removeQuote($string)
    {
        $string = trim($string);
        $string = str_replace("\'", "'", $string);
        $string = str_replace('"', "", $string);

        return $string;
    }
    public function parseXpath($contentHtml,$domain, $rule)
    {
        $temp = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);

        $ruleParse = $this->getRules($rule);

//        $ruleParse = $ruleParse . '/text()';
        $nodelist = $xpath->query($ruleParse);

        if($nodelist->length > 0){
            for($i=0;$i<$nodelist->length;$i++){
                $value = $nodelist->item($i)->nodeValue;
//                if ($domain == 'hc.com.vn') {
//                    $value = utf8_decode($value);
//                }
                if(!empty($value)){
                    array_push($temp,$this->removeQuote(trim($value)));
                }
            }
        }
        return $temp;
    }
}