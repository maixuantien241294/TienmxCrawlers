<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 1/3/2019
 * Time: 3:39 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerLinkRegex
{
    public $linkWebsite;
    public $rules;
    public $domain;
    public $rule_tag_a = '//a';

    public function __construct($linkWebsite, $rules, $domain)
    {
        $this->linkWebsite = $linkWebsite;
        $this->rules = $rules;
        $this->domain = $domain;
    }

    public function remove_html($description)
    {
        $description = preg_replace("/<script.*?\/script>/s", "", $description);
        $description = preg_replace("/<img[^>]+\>/i", "", $description);
        $description = preg_replace("/<link[^>]+\>/i", "", $description);
        $description = preg_replace("/<head[^>]+\>/i", "", $description);
        return $description;
    }

    public function crawler($content)
    {
        $return = ['message' => "", 'error' => true, 'data' => []];
        try {
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);
            $crawler = new \DOMXPath($html);
            $nodelist = $crawler->query($this->rule_tag_a);
            $newHtml = [];
            if (!empty($nodelist)) {
                foreach ($nodelist as $item) {
                    $href = trim($item->getAttribute('href'));
                    if (!empty($href)) {
                        array_push($newHtml, $href);
                    }
                }
            }
            $temp = [];
            if (!empty($newHtml)) {
                $newHtml = array_unique($newHtml);
                $link  = preg_grep ($this->rules, $newHtml);
                $link = $this->required_url($link);
                if (!empty($link)) {
                    foreach ($link as $href) {
                        /**
                         * @kiem tra domain
                         */
                        preg_match('/(jpg|jpeg|png|JPG|PNG|JPEG|GIF|gif|charset)/', $href, $matchImg);
                        if (!empty($matchImg)) {
                            continue;
                        }
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
                            'text' => $this->domain,
                        ];
                        if (!empty($dataParser)) {
                            array_push($temp, $dataParser);
                        }

                    }
                }
            }
            if (!empty($temp)) {
                $return['error'] = false;
                $return['data'] = $temp;
                $return['message'] = MSG_SUCCESS;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $temp;
    }

    protected function required_url($data)
    {
        try {
            $list_link = [];

            foreach ($data as $item) {
                preg_match('/' . $this->domain . '/', $item, $match);

                if (!empty($match)) {
                    /**
                     * @neu khong ton tai path thi bo qua
                     */
                    $path = parse_url($item);
                    /**
                     * @remove subdomain
                     */
                    $saveLinnk = true;
                    if (!isset($path['host'])) continue;

                    preg_match('/www/', $path['host'], $matchHost);
                    if (strlen($path['host']) != strlen($this->domain) && empty($matchHost)) {
                        $saveLinnk = false;
                    } else {
                        if (isset($path['path']) && strlen($path['path']) > 10) {
                            $saveLinnk = true;
                        } else {
                            $saveLinnk = false;
                        }
                    }
                    if ($saveLinnk == true) {
                        array_push($list_link, $item);
                    }
                } else {
                    $path1 = parse_url($item);
                    if (strlen($item) > 10 && strlen($item) < 500 && !isset($path1['host'])) {
                        array_push($list_link, $item);
                    }
                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        return $list_link;
    }
}