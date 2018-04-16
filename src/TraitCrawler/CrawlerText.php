<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:06 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerText
{
    use BaseTrait;
    public function executeText($xpath, $rule, $valueRemove)
    {
        $htmlString = "";
        $ruleParse = $this->getRules($rule);
        $nodelist = $xpath->query($ruleParse);
        if ($nodelist->length > 0) {
            $htmlString = trim($nodelist->item(0)->nodeValue);
        }
        $htmlString = $this->removeValue($valueRemove, '', $htmlString);
        return $htmlString;
    }
}