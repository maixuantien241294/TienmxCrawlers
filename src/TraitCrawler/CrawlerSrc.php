<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:05 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerSrc
{
    use BaseTrait;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    /**
     * @param $xpath
     * @param $rule
     * @param $tagsSrc
     * @param $linkWebsite
     * @param $domain
     * @param $valueRemove
     * @return array
     */
    public function executeSrc($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download)
    {

        $htmlString = [];
        $check = $this->checkXpath($rule);

        if ($check === false) {
            $htmlString = $this->parseDom($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download);
        } else {
            $htmlString = $this->parseXpath($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download);
        }

        return $htmlString;

    }

    protected function parseDom($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download)
    {
        $htmlString = [];
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $tagsSrc = explode(',', $tagsSrc);

        $explodeLink = explode('/', $linkWebsite);
        if (count($explodeLink) === 4) {
            $linkWebsite = substr($linkWebsite, 0, strlen($linkWebsite) - 1);
        }

        if (count($element)) {
            foreach ($element as $item) {
                for ($i = 0; $i < count($tagsSrc); $i++) {
                    $image = $item->getAttribute($tagsSrc[$i]);
                    if (!empty($image)) {
                        if (!preg_match('/' . $domain . '/', $image, $match)
                            && empty(parse_url($image, PHP_URL_HOST)) && !empty($image)) {
                            $image = $linkWebsite . $image;
                        }
                        array_push($htmlString, $image);
                    }
                }
            }
        }
        return $htmlString;
    }

    protected function parseXpath($contentHtml, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download)
    {
        /**
         * @remove '/' cuối của $linkWebsite
         */
        $htmlString = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);

        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $explodeLink = explode('/', $linkWebsite);
        if (count($explodeLink) === 4) {
            $linkWebsite = substr($linkWebsite, 0, strlen($linkWebsite) - 1);
        }

        $ruleParse = $this->getRules($rule);
        $tagsSrc = explode(',', $tagsSrc);
        foreach ($tagsSrc as $item) {
            $ruleImg = $ruleParse . '/@' . $item;
            $nodelist = $xpath->query($ruleImg);
            if ($nodelist->length > 0) {

                foreach ($nodelist as $key => $node) {
                    $image = $nodelist->item($key)->value;

                    if (!preg_match('/' . $domain . '/', $image, $match)
                        && empty(parse_url($image, PHP_URL_HOST)) && !empty($image)) {
                        $image = $linkWebsite . $image;
                    }
                    array_push($htmlString, $image);
                }
                break;
            }
        }
        return $htmlString;
    }
}