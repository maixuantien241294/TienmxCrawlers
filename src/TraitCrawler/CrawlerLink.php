<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 10/15/2018
 * Time: 12:24 AM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerLink
{
    use BaseTrait;

    public function executeLink($contentHtml, $domain, $linkWebsite, $rule)
    {
        $result = "";
        try {
            $ruleHtml = $this->getRuleHtml($rule);
            if (!empty($ruleHtml)) {
                for ($i = 0; $i < count($ruleHtml); $i++) {
                    $result = $this->xpath($contentHtml, $domain, $linkWebsite, $ruleHtml[$i]);
                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        return $result;
    }

    public function xpath($contentHtml, $domain, $linkWebsite, $rule)
    {
        $href = "";
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);
        $nodelist = $xpath->query($rule);
        if ($nodelist->length > 0) {
            $href = $nodelist->item(0)->getAttribute('href');
            $href = $this->__check_url($href, $domain, $linkWebsite);
        }
        return $href;
    }
}