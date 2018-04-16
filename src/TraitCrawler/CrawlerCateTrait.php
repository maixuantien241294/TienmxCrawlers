<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 11:22 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


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

        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);
        $crawler = new \DOMXPath($html);
        $nodelist = $crawler->query($this->rules);
        $temp = [];
        if ($nodelist->length > 0) {
            foreach ($nodelist as $item) {

                $href = $item->getAttribute('href');
                if (!preg_match('/' . $this->domain . '/', $href, $match)
                    && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                    $href = $this->linkWebsite . $href;
                }
                $dataParser = [
                    'href' => $href,
                    'text' => trim($item->nodeValue),
                ];
                array_push($temp, $dataParser);
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

        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);

        $crawler = new \DOMXPath($html);
        $nodelist = $crawler->query($this->rules);
        $temp = [];

        if ($nodelist->length > 0) {
            foreach ($nodelist as $item) {
                $href = $item->getAttribute('href');
                $explodeLink = explode('/', $this->linkWebsite);
                if (count($explodeLink) === 4) {
                    $this->linkWebsite = substr($this->linkWebsite, 0, strlen($this->linkWebsite) - 1);
                }
                if (!preg_match('/' . $this->domain . '/', $href, $match)
                    && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                    $href = $this->linkWebsite . $href;
                }
                $title = trim($item->getAttribute('title'));

                $dataParser = [
                    'href' => $href,
                    'text' => empty($title) ? trim($item->nodeValue) : $title,
                ];
                array_push($temp, $dataParser);
            }
        }
        return $temp;
    }

}