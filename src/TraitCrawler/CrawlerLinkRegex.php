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
            /**
             * @lấy tất cả link thẻ a
             */
            //'#\bhttps://weshop.com.vn/[.a-z0-9-]+/item/[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))+.html#'
            $temp = [];
            $content = $this->remove_html($content);
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $content);
            $xpath = new \DOMXPath($html);
            $nodelist = $xpath->evaluate($this->rule_tag_a);

            preg_match_all($this->rules, $content, $matchs);
            if (count($matchs) > 1) {
                $link = isset($matchs[0]) ? $matchs[0] : [];
                $link = array_unique($link);
                $link = $this->required_url($link);

                foreach ($link as $href) {
                    /**
                     * @kiem tra domain
                     */
                    preg_match('/(jpg|jpeg|png|JPG|PNG|JPEG|GIF|gif|charset)/', $href, $matchImg);
                    if(!empty($matchImg)){
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

    protected function required_url($data)
    {
        $list_link = [];
        $listImage = ['.png', '.jpg', '.PNG', '.JPG', '.jpeg', '.JPEG'];
        foreach ($data as $item) {
//            $item = '//support.leflair.vn/hc/vi/articles/214167448-ChĂ­nh-sĂ¡ch-tráº£-hĂ ng-vĂ -hoĂ n-tiá»n';
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
                if (strlen($item) > 10 && strlen($item) < 300 && !isset($path1['host'])) {
                    array_push($list_link, $item);
                }
            }
        }
        return $list_link;
    }
}