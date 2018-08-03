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
    public function parseRulesCategory($content, $paramsRemove = null)
    {
        try {
            if (empty($content)) {
                return [];
            }
            /**
             * @remove '/' cuối của $linkWebsite
             */
            $this->linkWebsite = $this->get_url($this->linkWebsite);
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
                            $href = isset($attr['href']) ? trim($attr['href']) : "";
                            if (!empty($href)) {
                                if (!preg_match('/' . $this->domain . '/', $href, $match)
                                    && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                                    /**
                                     * @kiêm tra xem phần tử đầu có dấu '/'
                                     */
                                    $testElement = substr($href, '0', 1);
                                    if ($testElement != '/') {
                                        $href = $this->linkWebsite . '/' . $href;
                                    } else {
                                        $href = $this->linkWebsite . $href;
                                    }
                                    //$href = $this->linkWebsite . $href;
                                } else {
                                    /**
                                     * @kiểm tra xem có domain không
                                     */
                                    $parse = parse_url($href);
                                    if (!isset($parse['host'])) {
                                        $testElement = substr($href, '0', 1);
                                        if ($testElement != '/') {
                                            $href = $this->linkWebsite . '/' . $href;
                                        } else {
                                            $href = $this->linkWebsite . $href;
                                        }
                                    }
                                    if (!preg_match("~^(?:f|ht)tps?://~i", $href)) {
                                        $href = "http:" . $href;
                                    }
                                }
                                if (!empty($paramsRemove)) {
                                    $remove = explode(',', $paramsRemove);
                                    if (!empty($remove)) {
                                        $href = $this->formatLink($href, $remove);
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
                }
            } else {
                $html = new \DOMDocument();
                @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);
                $crawler = new \DOMXPath($html);

                $nodelist = $crawler->query($this->rules);

                if ($nodelist->length > 0) {
                    foreach ($nodelist as $item) {
                        $href = trim($item->getAttribute('href'));
                        if (!empty($href)) {
                            if (!preg_match('/' . $this->domain . '/', $href, $match)
                                && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                                /**
                                 * @kiêm tra xem phần tử đầu có dấu '/'
                                 */
                                $testElement = substr($href, '0', 1);
                                if ($testElement != '/') {
                                    $href = $this->linkWebsite . '/' . $href;
                                } else {
                                    $href = $this->linkWebsite . $href;
                                }
//                                $href = $this->linkWebsite . $href;
                            } else {
                                /**
                                 * @kiểm tra xem có domain không
                                 */
                                $parse = parse_url($href);
                                if (!isset($parse['host'])) {
                                    $testElement = substr($href, '0', 1);
                                    if ($testElement != '/') {
                                        $href = $this->linkWebsite . '/' . $href;
                                    } else {
                                        $href = $this->linkWebsite . $href;
                                    }
                                }
                                if (!preg_match("~^(?:f|ht)tps?://~i", $href)) {
                                    $href = "http:" . $href;
                                }
                            }
                            if (!empty($paramsRemove)) {
                                $remove = explode(',', $paramsRemove);
                                if (!empty($remove)) {
                                    $href = $this->formatLink($href, $remove);
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
            }
        } catch (\Exception $exception) {
            var_dump($exception->getTraceAsString());
            dd($exception->getMessage());
        }
        return $temp;
    }

    /**
     * @param $content
     * @return array
     */
    public function parseRuleItemByCategory($content)
    {
        if (empty($content)) {
            return [];
        }
        /**
         * @remove '/' cuối của $linkWebsite
         */
        $this->linkWebsite = $this->get_url($this->linkWebsite);
        $explodeLink = explode('/', $this->linkWebsite);
        if (count($explodeLink) === 4) {
            $this->linkWebsite = substr($this->linkWebsite, 0, strlen($this->linkWebsite) - 1);
        }
        /**
         * @desc Check is dom or xpath
         */

        $ruleHtml = $this->getRuleHtml($this->rules);

        $tempAll = [];
        if (!empty($ruleHtml)) {
            for ($i = 0; $i < count($ruleHtml); $i++) {
                $ruleHtml[$i] = trim($ruleHtml[$i]);
                if (!empty($ruleHtml[$i])) {
                    $temp = [];
                    $check = $this->checkXpath($ruleHtml[$i]);
                    if ($check === false) {
                        $temp = $this->__parseItemByDom($ruleHtml[$i], $content);
                    } else {
                        $temp = $this->__parseItemXpath($ruleHtml[$i], $content);
                    }
                    if (!empty($temp)) ;
                    for ($n = 0; $n < count($temp); $n++) {
                        array_push($tempAll, $temp[$n]);
                    }
                }

            }
        }
        return $tempAll;
    }

    /**
     * @param $rules
     * @param $content
     */
    public function __parseItemByDom($rules, $content)
    {
        $temp = [];
        $dom = HtmlDomParser::str_get_html($content);
        $element = $dom->find($rules);
        if (count($element) > 0) {
            foreach ($element as $item) {
                $href = trim($item->getAttribute('href'));
                if (!empty($href)) {
                    if (!preg_match('/' . $this->domain . '/', $href, $match)
                        && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                        /**
                         * @kiêm tra xem phần tử đầu có dấu '/'
                         */
                        $testElement = substr($href, '0', 1);
                        if ($testElement != '/') {
                            $href = $this->linkWebsite . '/' . $href;
                        } else {
                            $href = $this->linkWebsite . $href;
                        }

                    } else {
                        /**
                         * @kiểm tra xem có domain không
                         */
                        $parse = parse_url($href);
                        if (!isset($parse['host'])) {
                            $testElement = substr($href, '0', 1);
                            if ($testElement != '/') {
                                $href = $this->linkWebsite . '/' . $href;
                            } else {
                                $href = $this->linkWebsite . $href;
                            }
                        }
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
        }
        return $temp;
    }

    public function __parseItemXpath($rules, $content)
    {
        $temp = [];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);
        $crawler = new \DOMXPath($html);
        $nodelist = $crawler->query($rules);
        
        if ($nodelist->length > 0) {
            foreach ($nodelist as $item) {
                $href = trim($item->getAttribute('href'));

                if (!empty($href)) {
                    if (!preg_match('/' . $this->domain . '/', $href, $match)
                        && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
                        /**
                         * @kiêm tra xem phần tử đầu có dấu '/'
                         */
                        $testElement = substr($href, '0', 1);
                        if ($testElement != '/') {
                            $href = $this->linkWebsite . '/' . $href;
                        } else {
                            $href = $this->linkWebsite . $href;
                        }
                        //$href = $this->linkWebsite . $href;
                    } else {
                        /**
                         * @kiểm tra xem có domain không
                         */
                        $parse = parse_url($href);
                        if (!isset($parse['host'])) {
                            $testElement = substr($href, '0', 1);
                            if ($testElement != '/') {
                                $href = $this->linkWebsite . '/' . $href;
                            } else {
                                $href = $this->linkWebsite . $href;
                            }
                        }
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

    public function get_url($link)
    {
        $url = $link;
        $result = parse_url($link);
        if (isset($result['scheme']) && isset($result['host'])) {
            $url = $result['scheme'] . '://' . $result['host'];
        }
        return $url;
    }


    public function formatLink($url = '', $rm_query = [])
    {
        $result = $url;
        $array_url = parse_url($url);
        if (isset($array_url['scheme']) && isset($array_url['host'])) {
            $result = $array_url['scheme'] . '://' . $array_url['host'];
            if (isset($array_url['path'])) {
                $result = $result . $array_url['path'];
                if (isset($array_url['query'])) {
                    $query = $array_url['query'];
                    parse_str($query, $parameters);
                    if (is_array($rm_query) && !empty($rm_query)) {
                        foreach ($rm_query as $item_query) {
                            if (isset($parameters[$item_query])) {
                                unset($parameters[$item_query]);
                            }
                        }
                        $query = http_build_query($parameters);
                        if (!empty($query)) {
                            $result .= '?' . $query;
                        }
                    }
                }
            } else {
                $result = $result . '/';
            }
        }
        return $result;
    }

    public function getRuleHtml($rule)
    {
        $listRule = [];

        $ruleData = explode('|', $rule);
        if (count($ruleData) > 0) {
            for ($i = 0; $i < count($ruleData); $i++) {
                $ruleData[$i] = trim($ruleData[$i]);
                if (!empty($ruleData[$i])) {
                    $newRule = $this->getRules($ruleData[$i]);
                    array_push($listRule, $newRule);
                }

            }
        }
        return $listRule;
    }

    public function getRules($rule)
    {
        $ruleParse = $rule;
        $ruleData = explode(',', $rule);
        if (count($ruleData) > 0) {
            $ruleParse = $ruleData[count($ruleData) - 1];
        }
        return $ruleParse;
    }
}