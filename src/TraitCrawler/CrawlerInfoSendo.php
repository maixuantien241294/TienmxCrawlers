<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 8/16/2018
 * Time: 5:09 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerInfoSendo
{
    public $text = 'text';
    public $html = 'html';
    public $price = 'price';
    public $plaintext = 'plaintext';
    public $content = 'content';
    public $src = 'src';
    public $misdn = 'phone_number';
    public $thong_so_ky_thuat = 'thong_so_ky_thuat';
    public $thong_tin_shop = 'thong_tin_shop';

    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload
    use CrawlerTypeTrait;
    public $cdn = "https://cf.shopee.vn/file/";
    public $domain = 'sendo.vn';
    public $api = 'https://www.sendo.vn/m/wap_v2/full/san-pham';
    public $mode = 100000;
    public $linkwebsite = 'https://www.sendo.vn/';
//    public $apiShop =''
    public $header = [];

    public function crawler($rules, $link, $domain, $linkWebsite, $cateId = 0, $download = 2)
    {
        $return = ['error' => true, 'message' => "lỗi hệ thống", 'content' => ""];
        $meta = [];
        $temp = [];
        try {
            $expLink = explode($this->domain, $link);
            if (!empty($expLink)) {
                $params = end($expLink);
                /**
                 * @kiểm tra export
                 */
                $checkParam = explode('san-pham', $params);
                if (count($checkParam) > 1) {
                    $params = end($checkParam);
                }
                $testElementData = substr( $params, '0', 1);
                if ($testElementData != '/') {
                    $linkApi = $this->api. '/' . $params;
                } else {
                    $linkApi = $this->api . $params;
                }
                $linkApi = str_replace('.html', "", $linkApi);
                
                $res = $this->__getContent($linkApi);

                if ($res['http_code'] == 200) {
                    $data = json_decode($res['content'], true);

                    if ($data['status']['code'] == 200) {
                        $result = $data['result']['data'];
                        $resultMeta = $data['result']['meta_data'];
                        if (!empty($result)) {
                            for ($i = 0; $i < count($rules); $i++) {
                                $rules[$i]['content'] = [];
                                if ($rules[$i]['key'] == 'pro_name') {
                                    $name = $result['name'];
                                    $skuName = (isset($result['sku_user']) && !empty($result['sku_user'])) ? ' - ' . $result['sku_user'] : "";
                                    if (!empty($skuName)) {
                                        $name = $name . $skuName;
                                    }
                                    $rules[$i]['content'] = [
                                        $name
                                    ];
                                }

                                if ($rules[$i]['key'] == 'pro_picture') {
                                    $rules[$i]['content'] = [];
                                    if (isset($result['media'][0]) && !empty($result['media'][0]) && isset($result['media'][0]['image'])) {
                                        $rules[$i]['content'] = [
                                            urlencode($result['media'][0]['image'])
                                        ];
                                    }else if (isset($result['media'][1]) && !empty($result['media'][1]) && isset($result['media'][1]['image'])){
                                        $rules[$i]['content'] = [
                                            urlencode($result['media'][1]['image'])
                                        ];
                                    }

                                }
                                if ($rules[$i]['key'] == 'pro_price') {
                                    $rules[$i]['content'] = [
                                        doubleval($result['price'])
                                    ];

                                }
                                if ($rules[$i]['key'] == 'pro_price_promotion') {
                                    $rules[$i]['content'] = [
                                        doubleval($result['final_price'])
                                    ];

                                }
                                if ($rules[$i]['key'] == 'pro_list_image') {
                                    $listImg = [];
                                    if (!empty($result['media'])) {
                                        foreach ($result['media'] as $media) {
                                            if(isset($media['image'])){
                                                array_push($listImg, urlencode($media['image']));
                                            }
                                        }
                                    }
                                    $rules[$i]['content'] = $listImg;
                                }
                                if ($rules[$i]['key'] == 'pro_description') {
                                    $rules[$i]['content'] = $result['description'];

                                }
                                if ($rules[$i]['key'] == 'pro_misdn_shop') {
                                    if (!empty($result['shop_info'])) {
                                        $rules[$i]['content'] = [
                                            isset($result['shop_info']['phone_number']) ? $result['shop_info']['phone_number'] : ""
                                        ];
                                    }
                                }

                                if ($rules[$i]['key'] == 'pro_thong_so_ky_thuat') {
                                    $listThongSo = [];
                                    if (!empty($result['description'])) {
                                        $ruleTenThongSo = '//div[@class="attrs-block"]/ul/li/strong';
                                        $ruleGiaTriThongSo = '//div[@class="attrs-block"]/ul/li/span';
                                        $cGiaTriThongSo = new CrawlerGiaTriThongSoKyThuat();
                                        $giaTriThongSo = $cGiaTriThongSo->crawler($result['description'], $domain, $ruleGiaTriThongSo);
                                        $cTenThongSo = new CrawlerTenThongSo();
                                        $tenThongSo = $cTenThongSo->crawler($result['description'], $domain, $ruleTenThongSo);

                                        if (!empty($tenThongSo) && !empty($giaTriThongSo)) {
                                            for ($k = 0; $k < count($tenThongSo); $k++) {
                                                $data = [
                                                    $tenThongSo[$k] => isset($giaTriThongSo[$k]) ? $giaTriThongSo[$k] : ""
                                                ];
                                                array_push($listThongSo, $data);
                                            }
                                        }
                                    }
                                    $rules[$i]['content'] = $listThongSo;
                                }
                                if ($rules[$i]['key'] == 'pro_logo_website') {

                                    if (!empty($result['shop_info'])) {
                                        $img = str_replace('_120x60', '_300x300', $result['shop_info']['shop_logo']);
                                        if (!empty($img)) {
                                            $rules[$i]['content'] = [$img];
                                        }
                                    }
                                }
                                if ($rules[$i]['key'] == 'pro_infor_shop') {
                                    if (!empty($result['shop_info'])) {
                                        $data = [
                                            'text' => $result['shop_info']['shop_name'],
                                            'href' => $this->linkwebsite . $result['shop_info']['shop_url']
                                        ];
                                        $rules[$i]['content'] = [
                                            $data
                                        ];
                                    }
                                }
                                if ($download == $this->isDownload) {
//                                    if (in_array($rules[$i]['type'], [$this->src, $this->price])) {
//                                        $temp[$rules[$i]['key']] = json_encode($rules[$i]['content']);
//                                    } else {
//
//                                    }
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
                                'content' => $resultMeta['description']
                            ]);
                            array_push($meta, [
                                'key' => 'meta_title',
                                'name' => 'meta_title',
                                'type' => 'text',
                                'content' => $resultMeta['page_title']
                            ]);
                            array_push($meta, [
                                'key' => 'image_seo',
                                'name' => 'image_seo',
                                'type' => 'src',
                                'content' => [
                                    $resultMeta['og_image']
                                ]
                            ]);
                            array_push($meta, [
                                'key' => 'keywords',
                                'name' => 'keywords',
                                'type' => 'text',
                                'content' => $resultMeta['keywords']
                            ]);
                        }
                    }
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
            $return['content'] = $exception->getMessage();
        }
        return $return;
    }
}