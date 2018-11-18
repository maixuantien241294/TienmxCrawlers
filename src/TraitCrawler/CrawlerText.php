<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:06 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerText
{
    use BaseTrait;

    public function executeText($contentHtml, $rule, $valueRemove)
    {
        $htmlString = [];
        try {
            $ruleHtml = $this->getRuleHtml($rule);
            if (!empty($ruleHtml)) {
                for ($i = 0; $i < count($ruleHtml); $i++) {
                    $check = $this->checkXpath($ruleHtml[$i]);
                    if ($check === false) {

                        $newString = $this->parseDom($contentHtml, $ruleHtml[$i], $valueRemove);
                    } else {
                        $newString = $this->parseXpath($contentHtml, $ruleHtml[$i], $valueRemove);
                    }

                    if (!empty($valueRemove)) {
                        $newString = $this->removeValue($valueRemove, '', $newString);
                    }
                    if(!empty(trim($newString))){
                        array_push($htmlString, trim($newString));
                    }
                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

        return $htmlString;
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
        $htmlString = $this->removeValue($valueRemove, '', $htmlString);
        return $htmlString;
    }
}