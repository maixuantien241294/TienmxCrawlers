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
    public $isDownload = 1; //crawler để download
    public $domain = 'shopee.vn';
    public $api = 'https://shopee.vn/api/v1/item_detail';
    public $linkApiShop = 'https://shopee.vn/api/v1/shops/';
    public $mode = 100000;
    public $header = [
        'Host: shopee.vn',
        'If-None-Match-: 55b03-f0dfcf8fc996c570023bd5901436de8b',
        'Origin: https://shopee.vn',
        "X-Forwarded-For:150.95.104.211"
    ];
    public $headerShop = [
        'Host: shopee.vn',
        'REMOTE_ADDR:150.95.104.211',
        "X-Forwarded-For:150.95.104.211"
        , 'Connection: keep-alive'
        , 'Content-Length: 23'
        , 'Origin: https://shopee.vn'
        , 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'
        , 'Content-Type: application/json'
        , 'Accept: application/json'
        , 'X-Requested-With: XMLHttpRequest'
        , 'If-None-Match-: 55b03-f0dfcf8fc996c570023bd5901436de8b'
        , 'X-API-SOURCE: pc'
        , 'X-CSRFToken: Bm1ILH7dIAZGY85ICldUTqBpT1CEsX88'
        , 'Referer:  https://shopee.vn/C%C3%A1p-SS-v%C3%A2y-c%C3%A1-%C4%91%E1%BA%A7u-ngang-i.77904874.1371366664'
        , 'Accept-Encoding: gzip, deflate, br'
        , 'Accept-Language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5'
        , 'Cookie: SPC_IA=-1; SPC_F=K2elwW8MIkwQOu7SwDzWIGYNcUskUoeS; REC_T_ID=c4ffd4fc-9d14-11e8-ad31-1866dabbffa2; ga=GA1.2.38918513.1533957315; cto_lwid=f6682526-3ef2-4d6e-bf79-b44eb94b56c7; SPC_EC="eC7sRJcG1aFGD/MpiandrpgctO3tGjjWF8CcE64oRl1V49GLQluSSWIUinQGs6MLQb9f4Qme0bsh84/7/dZ3s+LmLLPns0CkVGKOJXGTURkzBYi79qVs+TMEVGMfrCQFmFjefq9H+A87Dfwi5A+HbA=="; SPC_T_ID="f8knQjhyp7q2zxgk+pRgLRP3Id4AsxE/n1Cz1hhV0uHF90hDfxahRyZbSoKc5iKo6RYnvkXcB2CJpYiwR+opIuniKgSCqQycmVj6SgMJ2BE="; SPC_U=11479531; SPC_T_IV="NU/A8Yyc/ueRHoNHwcIiqQ=="; SPC_SC_TK=89efb03f08ee650667b57d26a6a4350f; SPC_SC_UD=11479531; gid=GA1.2.1438902529.1534053960; SPC_SI=fxmtpe9a8flbc5hlspvmg78lhf6kp9ys; aff_sid=FvD51bTrSFUV1IZZtZVrCPaCFDu83XIrXWXNCIc5qVzqSwyA; csrftoken=Bm1ILH7dIAZGY85ICldUTqBpT1CEsX88; bannerShown=true; gat=1'
    ];

    public function __construct()
    {
//        $this->header = [
//            'Host: shopee.vn',
//            'If-None-Match-: 55b03-987390ecdcf5cfd8f45c6dcbc4599dda'
//        ];
    }

    public function crawler($rules, $link, $domain, $linkWebsite, $cateId = 0, $download = 2)
    {
        $return = ['error' => true, 'message' => "lỗi hệ thống", 'content' => ""];
        $temp = [];
        $meta = [];
        $dataImg = [];
        try {
            $itemId = 0;
            $shopId = 0;
            $expLink = explode('.', $link);
            if (!empty($expLink)) {
                $itemId = intval(end($expLink));
                $shopId = isset($expLink[count($expLink) - 2]) ? intval($expLink[count($expLink) - 2]) : 0;
                if (intval($itemId) == 0 && intval($shopId) == 0) {
                    return $return;
                }

                $linkApi = $this->api . '?item_id=' . $itemId . '&shop_id=' . $shopId;
                $res = $this->getApiShopee($linkApi, $this->headerShop);
                dd($res);
                /**
                 * @call api shop
                 */
                $dataShop = [
                    'shop_ids' => [
                        $shopId
                    ]
                ];
                $resShop = $this->postApiShopee($this->linkApiShop, $dataShop, $this->headerShop);
//                dd($resShop);
                if ($res['http_code'] == 200) {
                    $dataRes = json_decode($res['content'], true);
                    dd($dataRes);
                    if (!empty($dataRes['images'])) {
                        $dataImg = explode(',', $dataRes['images']);
                    }
                    for ($i = 0; $i < count($rules); $i++) {
                        $rules[$i]['content'] = [];
                        if ($rules[$i]['key'] == 'pro_name') {
                            $rules[$i]['content'] = [
                                $dataRes['name']
                            ];
                        }

                        if ($rules[$i]['key'] == 'pro_picture') {
                            if (!empty($dataImg)) {
                                $rules[$i]['content'] = [
                                    $this->cdn . $dataImg[0]
                                ];
                            }

                        }
                        if ($rules[$i]['key'] == 'pro_price') {
                            $price = doubleval($dataRes['price_before_discount'] / $this->mode);
                            if (intval($dataRes['price_before_discount']) == 0) {
                                $price = doubleval($dataRes['price'] / $this->mode);
                            }
                            $rules[$i]['content'] = [
                                $price
                            ];
                        }
                        if ($rules[$i]['key'] == 'pro_price_promotion') {
                            $rules[$i]['content'] = [
                                doubleval($dataRes['price'] / $this->mode)
                            ];
                        }
                        if ($rules[$i]['key'] == 'pro_list_image') {
                            $listImg = [];
                            if (!empty($dataImg)) {
                                foreach ($dataImg as $item) {
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
                                    if (!empty($val['name'])) {
                                        $data = [
                                            $val['name'] => $val['value']
                                        ];
                                        array_push($dataAtri, $data);
                                    }
                                }
                            }
                            $rules[$i]['content'] = $dataAtri;
                        }
                        if ($resShop['http_code'] == 200) {
                            $dataResShop = json_decode($resShop['content'], true);
                            $dataResShop = isset($dataResShop[0]) ? $dataResShop[0] : [];
                            if (!empty($dataResShop)) {
                                if ($rules[$i]['key'] == 'pro_infor_shop') {
                                    $userName = isset($dataResShop['username']) ? $dataResShop['username'] : "";
                                    $href = !empty($userName) ? 'https://shopee.vn/' . $userName : "";
                                    $name = isset($dataResShop['name']) ? $dataResShop['name'] : "";
                                    if (!empty($href) && !empty($name)) {
                                        $data = [
                                            'text' => $name,
                                            'href' => $href,
                                            'place' => isset($dataResShop['place']) ? $dataResShop['place'] : ""
                                        ];
                                        $rules[$i]['content'] = [
                                            $data
                                        ];
                                    }
                                }
                                if ($rules[$i]['key'] == 'pro_logo_website') {

                                    if (!empty($dataResShop['portrait'])) {

                                        $rules[$i]['content'] = [$this->cdn . $dataResShop['portrait']];
                                    }
                                }
                            }
                        }
                        if ($download == $this->isDownload) {
                            $temp[$rules[$i]['key']] = $rules[$i]['content'];
                            if (intval($cateId) > 0) {
                                $temp['cate_id'] = intval($cateId);
                            }
                        } else {
                            $temp[$i] = $rules[$i];
                        }
                    }
                    /**
                     * @them meta
                     */
                    array_push($meta, [
                        'key' => 'description',
                        'name' => 'description',
                        'type' => 'text',
                        'content' => $dataRes['name']
                    ]);
                    array_push($meta, [
                        'key' => 'meta_title',
                        'name' => 'meta_title',
                        'type' => 'text',
                        'content' => $dataRes['name']
                    ]);
                    $imageSeo = "";
                    if (!empty($dataImg)) {
                        $imageSeo = $this->cdn . $dataImg[0];
                    }
                    array_push($meta, [
                        'key' => 'image_seo',
                        'name' => 'image_seo',
                        'type' => 'src',
                        'content' => [
                            $imageSeo
                        ]
                    ]);
                    array_push($meta, [
                        'key' => 'keywords',
                        'name' => 'keywords',
                        'type' => 'text',
                        'content' => $dataRes['name']
                    ]);
                }
            }
            if (!empty($temp)) {
                if (!empty($meta)) {
                    if ($download == $this->isDownload) {
                        $tempMeta = [];
                        foreach ($meta as $val) {
                            $tempMeta[$val['key']] = $val['content'];
                        }
                        $temp = array_merge($temp, $tempMeta);
                    } else {
                        $temp = array_merge($temp, $meta);
                    }

                }
                $return['content'] = $temp;
                $return['error'] = false;
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        return $return;
    }
}