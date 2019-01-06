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

    public function crawler($content)
    {
        $return = ['message' => "", 'error' => true, 'data' => []];
        try {
            /**
             * @lấy tất cả link thẻ a
             */
            //'#\bhttps://weshop.com.vn/[.a-z0-9-]+/item/[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))+.html#'
            $temp = [];
            preg_match_all($this->rules, $content, $matchs);
            if (count($matchs) > 1) {
                $link = isset($matchs[0]) ? $matchs[0] : [];
                $link = array_unique($link);
                foreach ($link as $href){
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
                    $dataParser = [
                        'href' => $href,
                        'text' => $this->domain,
                    ];
                    array_push($temp, $dataParser);
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
}