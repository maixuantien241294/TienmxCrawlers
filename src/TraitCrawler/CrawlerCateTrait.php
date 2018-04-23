<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 11:22 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerCateTrait
{
    public $linkWebsite;
    public $rules;
    public $domain;

    public function __construct($linkWebsite, $rules, $domain)
    {
        $this->linkWebsite = $linkWebsite;
        $this->rules = $rules;
        $this->domain = $domain;
    }

    /**
     * @param $content
     * @return array
     */
    public function parseRulesCategory($content)
    {
        /**
         * @remove '/' cuối của $linkWebsite
         */
        $explodeLink = explode('/', $this->linkWebsite);
        if (count($explodeLink) === 4) {
            $this->linkWebsite = substr($this->linkWebsite, 0, strlen($this->linkWebsite) - 1);
        }
        /**
         * @desc Check is dom or xpath
         */
        $check = $this->checkXpath($this->rules);
        $temp = [];
        if ($check === false) {
            $content = str_replace("\n", '', $content);
            $content = trim($content);

            $dom = HtmlDomParser::str_get_html($content);
            if ($dom != false) {
                $element = $dom->find($this->rules);
                if (!empty($element)) {
                    foreach ($element as $item) {
                        $attr = $item->attr;
                        $href = isset($attr['href']) ? $attr['href'] : "";
                        if (!preg_match('/' . $this->domain . '/', $href, $match)
                            && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                            $href = $this->linkWebsite . $href;
                        }else{
                            if (!preg_match("~^(?:f|ht)tps?://~i", $href)) {
                                $href = "http:" . $href;
                            }
                        }
                        $data = [
                            'href' => $href,
                            'text' => $item->text(),
                        ];
                        array_push($temp, $data);
                    }
                }
            }
        } else {
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);
            $crawler = new \DOMXPath($html);

            $nodelist = $crawler->query($this->rules);

            if ($nodelist->length > 0) {
                foreach ($nodelist as $item) {
                    $href = $item->getAttribute('href');
                    if (!preg_match('/' . $this->domain . '/', $href, $match)
                        && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                        $href = $this->linkWebsite . $href;
                    } else {
                        if (!preg_match("~^(?:f|ht)tps?://~i", $href)) {
                            $href = "http:" . $href;
                        }
                    }
                    $dataParser = [
                        'href' => $href,
                        'text' => trim($item->nodeValue),
                    ];
                    array_push($temp, $dataParser);
                }
            }
        }
        return $temp;
    }

    /**
     * @param $content
     * @return array
     */
    public function parseRuleItemByCategory($content)
    {
        /**
         * @remove '/' cuối của $linkWebsite
         */
        $explodeLink = explode('/', $this->linkWebsite);
        if (count($explodeLink) === 4) {
            $this->linkWebsite = substr($this->linkWebsite, 0, strlen($this->linkWebsite) - 1);
        }
        /**
         * @desc Check is dom or xpath
         */
        $check = $this->checkXpath($this->rules);
        $temp = [];

        if ($check === false) {

            //parse theo DOM
            //$this->rules = 'div#grid-container[class=tab-pane active]  div[class=category-product]  div[class=row]  div[class=col-sm-6]  div[class=products]  div[class=product]  div  h3  a';
            $dom = HtmlDomParser::str_get_html($content);
            $element = $dom->find($this->rules);
            if (count($element) > 0) {
                foreach ($element as $item) {
                    $href = $item->getAttribute('href');
                    if (!preg_match('/' . $this->domain . '/', $href, $match)
                        && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                        $href = $this->linkWebsite . $href;
                    }else{
                        if (!preg_match("~^(?:f|ht)tps?://~i", $href)) {
                            $href = "http:" . $href;
                        }
                    }
                    $dataParser = [
                        'href' => $href,
                        'text' => $item->text(),
                    ];
                    array_push($temp, $dataParser);
                }
            }
        } else {

            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);

            $crawler = new \DOMXPath($html);
            $nodelist = $crawler->query($this->rules);

            if ($nodelist->length > 0) {
                foreach ($nodelist as $item) {
                    $href = $item->getAttribute('href');

                    if (!preg_match('/' . $this->domain . '/', $href, $match)
                        && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                        $href = $this->linkWebsite . $href;
                    }else{
                        if (!preg_match("~^(?:f|ht)tps?://~i", $href)) {
                            $href = "http:" . $href;
                        }
                    }
                    $title = trim($item->getAttribute('title'));

                    $dataParser = [
                        'href' => $href,
                        'text' => empty($title) ? trim($item->nodeValue) : $title,
                    ];
                    array_push($temp, $dataParser);
                }
            }
        }

        return $temp;
    }

    public function checkXpath($rule)
    {
        $check = true;
        $strFirst = substr($rule, 0, 1);
        if (!empty($strFirst) && $strFirst != '/') {
            $check = false;
        }
        return $check;
    }
}