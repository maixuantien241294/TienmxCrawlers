<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 8/16/2018
 * Time: 5:09 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerInfoShopee
{
    use CrawlerTypeTrait;
    public $cdn = "https://cf.shopee.vn/file/";
    public $domain = 'shopee.vn';
    public $api = 'https://shopee.vn/api/v1/item_detail';
    public $mode = 100000;
//    public $apiShop =''
    public $header = [];

    public function __construct()
    {
        $this->header = [
            'Host: shopee.vn',
            'If-None-Match-: 55b03-987390ecdcf5cfd8f45c6dcbc4599dda'
        ];
    }

    public function crawler($rules, $link, $domain, $linkWebsite)
    {
        try {
            $itemId = 0;
            $shopId = 0;
            $expLink = explode('.', $link);
            if (!empty($expLink)) {
                $itemId = intval(end($expLink));
                $shopId = isset($expLink[count($expLink) - 2]) ? intval($expLink[count($expLink) - 2]) : 0;
                $linkApi = $this->api . '?item_id=' . $itemId . '&shop_id=' . $shopId;
                $res = $this->__getContent('https://shopee.vn/%C4%90%E1%BB%93ng-h%E1%BB%93-l%E1%BA%AFp-r%C3%A1p-d%C3%A0nh-cho-tr%E1%BA%BB-em-h%C3%ACnh-nh%C3%A2n-v%E1%BA%ADt-ho%E1%BA%A1t-h%C3%ACnh-i.64596093.1237361574',[
                    'web_user_agents'   =>  'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
                ]);

                if ($res['http_code'] == 200) {
                    $dataRes = json_decode($res['content'], true);
                    for ($i = 0; $i < count($rules); $i++) {
                        if ($rules[$i]['key'] == 'pro_name') {
                            $rules[$i]['content'] = [
                                $dataRes['name']
                            ];
                        }
                        if ($rules[$i]['key'] == 'pro_picture') {
                            $rules[$i]['content'] = [
                                $this->cdn . $dataRes['image']
                            ];
                        }
                        if ($rules[$i]['key'] == 'pro_price') {
                            $rules[$i]['content'] = [
                                doubleval($dataRes['price_before_discount'] / $this->mode)
                            ];
                        }
                        if ($rules[$i]['key'] == 'pro_price_promotion') {
                            $rules[$i]['content'] = [
                                doubleval($dataRes['price'] / $this->mode)
                            ];
                        }
                        if ($rules[$i]['key'] == 'pro_list_image') {
                            $listImg = [];
                            if (!empty($dataRes['image_list'])) {
                                foreach ($dataRes['image_list'] as $item) {
                                    array_push($listImg, $this->cdn . $item);
                                }
                            }
                            $rules[$i]['content'] = $listImg;
                        }
                        if ($rules[$i]['key'] == 'pro_description') {
                            $rules[$i]['content'] = $dataRes['description'];
                        }
                        /**
                         * @thong so ky thuat
                         */
                        if ($rules[$i]['key'] == 'pro_thong_so_ky_thuat') {
                            $dataAtri = [];
                            if (!empty($dataRes['attributes'])) {
                                foreach ($dataRes['attributes'] as $val) {
                                    $data = [
                                        $val['name'] => $val['value']
                                    ];
                                    array_push($dataAtri, $data);
                                }
                            }
                            $rules[$i]['content'] = $dataAtri;
                        }
                    }

                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        return $rules;
    }
}