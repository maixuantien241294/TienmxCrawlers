<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 5/25/2018
 * Time: 4:28 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


use Sunra\PhpSimple\HtmlDomParser;

class CrawlerMisdn
{
    use BaseTrait;
    public $regexReplace = [
        '*', '-', '(84)', '(0)','(+84)', ':', '\\', '/', '.', ' '
    ];
    public $regexPhone = '/(0|84)\d{9,11}$/';
    public $regexSwitchboard = '/(028|1800|1900)\d{3,11}$/';

    /**
     * @param $contentHtml
     * @param $rule
     * @param $tagsSrc
     * @param $linkWebsite
     * @param $domain
     * @param $valueRemove
     * @param $download
     */
    public function executeMisdn($contentHtml, $rule, $linkWebsite, $domain)
    {
        try {
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

                    /**
                     * @desc : Xử lý lấy số điện thoại
                     */
                    if (!empty($string)) {
                        $string = str_replace($this->regexReplace, '', $string);
                        if (preg_match_all('/\d+/', $string, $matches)) {

                            if (count($matches[0]) > 0) {
                                $tempMisdn = [];
                                for ($n = 0; $n < count($matches[0]); $n++) {
                                    preg_match($this->regexPhone, $matches[0][$n], $match);
                                    if (count($match) > 0) {
                                        array_push($tempMisdn, $match[0]);
                                    }
                                    preg_match($this->regexSwitchboard, $matches[0][$n], $match1);
                                    if (count($match1) > 0) {
                                        array_push($tempMisdn, $match1[0]);
                                    }
                                }
                                if (!empty($tempMisdn)) {
                                    for ($j = 0; $j < count($tempMisdn); $j++) {
                                        array_push($listMisdn, $tempMisdn[$j]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return array_unique($listMisdn);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }

    /**
     * @param $contentHtml
     * @param $rule
     * @param $linkWebsite
     * @param $domain
     */
    public function __parseDom($contentHtml, $rule, $linkWebsite, $domain)
    {
        $htmlString = "";
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        if (count($element) > 0) {
            foreach ($element as $item) {
                $htmlString = $htmlString . $item->text();
            }
        }
        $htmlString = trim($htmlString);
        return $htmlString;
    }

    /**
     * @param $contentHtml
     * @param $rule
     * @param $linkWebsite
     * @param $domain
     */
    public function _parseXpath($contentHtml, $rule, $linkWebsite, $domain)
    {
        $htmlString = "";
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);
        $ruleParse = $this->getRules($rule);
        $nodelist = $xpath->query($ruleParse);
        if ($nodelist->length > 0) {
            $htmlString = trim($nodelist->item(0)->nodeValue);
        }
        return $htmlString;

    }
}