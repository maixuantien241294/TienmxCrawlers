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
    public $regexReplace = [
        '*', '', '(', ')', ':', '\\', '/', '.', ' ', ','
    ];

    public function executePrice($contentHtml, $rule, $valueRemove)
    {
        $htmlString = "";
        //lấy tất cả là số trong chuỗi tring;
        try {
            $ruleHtml = $this->getRuleHtml($rule);
            if (!empty($ruleHtml)) {
                for ($i = 0; $i < count($ruleHtml); $i++) {
                    $ruleHtml[$i] = trim($ruleHtml[$i]);
                    if (!empty($ruleHtml[$i])) {
                        $check = $this->checkXpath($ruleHtml[$i]);
                        if ($check === false) {
                            $newString = $this->parseDom($contentHtml, $ruleHtml[$i], $valueRemove);
                        } else {
                            $newString = $this->parseXpath($contentHtml, $ruleHtml[$i], $valueRemove);
                        }
                        $htmlString = $htmlString . '-' . $newString;
                    }
                }
            }
            $price = [];
            /**
             * @desc => Kiểm tra nếu có dấu gạch ngang `-`
             */
            $htmlString = str_replace($this->regexReplace, '', $htmlString);

            $expPrice = explode('-', $htmlString);

            if (count($expPrice) >= 1) {
                foreach ($expPrice as $item) {
                    $priceItem = "";
                    if (!empty(trim($item))) {
                        if (preg_match_all('/\d+/', trim($item), $matches)) {

                            $matches = $matches[0];

                            foreach ($matches as $value) {
                                if (strlen($value) > 4){
                                    array_push($price, $value);
                                }
                            }
                        }

                    }

                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
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