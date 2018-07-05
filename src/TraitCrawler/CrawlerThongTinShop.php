<?php

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerThongTinShop
{
    use BaseTrait;

    public function executeInfoShop($contentHtml, $rule, $linkWebsite, $domain)
    {

        try {
            $listShop = [];

            $linkWebsite = $this->getUrl($linkWebsite);

            $listMisdn = [];
            $ruleHtml = $this->getRuleHtml($rule);
            if (!empty($ruleHtml)) {
                for ($i = 0; $i < count($ruleHtml); $i++) {
                    $check = $this->checkXpath($ruleHtml[$i]);
                    if ($check === false) {
                        $string = $this->__parseDom($contentHtml, $ruleHtml[$i], $linkWebsite, $domain);
                    } else {
                        $string = $this->_parseXpath($contentHtml, $ruleHtml[$i], $linkWebsite, $domain);
                    }
                    if (!empty($string)) {
                        if(isset($string[0])){
                            array_push($listShop, $string[0]);
                        }

                    }
                }
            }
            return $listShop;
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }

    public function __parseDom($contentHtml, $rule, $linkWebsite, $domain)
    {
        $listShop = [];
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        if (count($element) > 0) {
            foreach ($element as $item) {
                $href = trim($item->getAttribute('href'));
                if (!empty($href)) {
                    $href = $this->__check_url($href, $domain, $linkWebsite);
                }
                $dataParser = [
                    'href' => $href,
                    'text' => trim($item->text()),
                ];
                array_push($listShop, $dataParser);
            }
        }
        return $listShop;
    }

    public function _parseXpath($contentHtml, $rule, $linkWebsite, $domain)
    {
        $listShop = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $crawler = new \DOMXPath($html);
        $nodelist = $crawler->query($rule);
        if ($nodelist->length > 0) {
            foreach ($nodelist as $item) {
                $href = trim($item->getAttribute('href'));
                if (!empty($href)) {
                    $href = $this->__check_url($href, $domain, $linkWebsite);
                }
                $title = trim($item->getAttribute('title'));
                $dataParser = [
                    'href' => $href,
                    'text' => empty($title) ? trim($item->nodeValue) : $title,
                ];
                array_push($listShop, $dataParser);
            }
        }
        return $listShop;
    }
}