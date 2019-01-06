<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12/26/2018
 * Time: 9:47 AM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerInfoLotteV2
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
    public $cdn = "https://cdn.lotte.vn/media/catalog/product";
    public $cdn_v2 = 'cdn.lotte.vn';
    public $domain = 'sendo.vn';
    public $api = 'https://www.lotte.vn/rest/V1/lotte_product/details';
    public $api_v2 = 'https://www.lotte.vn/api/v1/products';
    public $mode = 100000;
    public $linkwebsite = 'https://www.lotte.vn/';
    public $linkShopWeb = 'https://www.lotte.vn/seller/';
//    public $apiShop =''
    public $header = [];

    public function crawler($rules, $idPro, $link, $domain, $linkWebsite, $cateId = 0, $download = 2)
    {
        $return = ['error' => true, 'message' => "lỗi hệ thống", 'content' => ""];
        $meta = [];
        $temp = [];
        $linkApi = "";
        try {
            $exp = explode('product/', $link);

            $id = 0;
            if (intval($idPro) > 0) {
                $linkApi = $this->api_v2 . '/' . $idPro . '/detail';
            } else {
                if (!empty($exp) && isset($exp[1])) {
                    $expId = explode('/', $exp[1]);
                    if (!empty($expId)) {
                        $id = isset($expId[0]) ? intval($expId[0]) : 0;
                        $linkApi = $this->api_v2 . '/' . $idPro . '/detail';
                    }
                } else {
                    $exp1 = explode('/', $link);
                    $expIdEnd = end($exp1);
                    $expDot = explode('.', $expIdEnd);
                    if (!empty($expDot) && isset($expDot[0])) {
                        $id = isset($expDot[0]) ? intval($expDot[0]) : 0;
                        $linkApi = $this->api_v2 . '/' . $idPro . '/detail';
                    }

                }
            }
            $res = $this->__getContent($linkApi);
            if ($res['http_code'] == 200) {
                $result = json_decode($res['content'], true);
                if (!empty($result)) {
                    for ($i = 0; $i < count($rules); $i++) {
                        $rules[$i]['content'] = [];
                        if ($rules[$i]['key'] == 'pro_name' && !empty($result['name'])) {
                            $rules[$i]['content'] = [
                                $result['name']
                            ];
                        }
                        if ($rules[$i]['key'] == 'pro_picture') {
                            $listArrayimage = [];
                            if (isset($result['media_gallery']['big'])) {
                                for ($img = 0; $img < count($result['media_gallery']['big']); $img++) {
                                    $newImage = $result['media_gallery']['big'][$img];
                                    if (strpos($newImage, $this->cdn_v2) == false) {
                                        $newImage = $this->cdn . '/' . $newImage;
                                    }
                                    array_push($listArrayimage, urlencode($newImage));
                                }
                            }
                            $rules[$i]['content'] = $listArrayimage;
                        }
                        if ($rules[$i]['key'] == 'pro_price') {

                            if (isset($result['price']['VND']['price']) && intval($result['price']['VND']['price']) > 0) {
                                $rules[$i]['content'] = [
                                    doubleval($result['price']['VND']['price'])
                                ];
                            }
                        }
                        if ($rules[$i]['key'] == 'pro_price_promotion') {
                            if (isset($result['price']['VND']['default']) && intval($result['price']['VND']['default']) > 0) {
                                $rules[$i]['content'] = [
                                    doubleval($result['price']['VND']['default'])
                                ];
                            }
                        }
                        if ($rules[$i]['key'] == 'pro_list_image') {
                            $listImg = [];
                            if (isset($result['media_gallery']['big'])) {
                                for ($img = 0; $img < count($result['media_gallery']['big']); $img++) {
                                    $newImg = $result['media_gallery']['big'][$img];
                                    if (strpos($newImage, $this->cdn_v2) == false) {
                                        $newImage = $this->cdn . '/' . $newImage;
                                    }
                                    array_push($listImg, urlencode($newImg));
                                }
                                $rules[$i]['content'] = $listImg;
                            }
                        }
                        if ($rules[$i]['key'] == 'pro_description') {
                            $desc = isset($result['description']) ? $result['description'] : "";
                            $dataImageSearch = [
                                'data-original',
                                'data-src'
                            ];
                            $dataImgReplace = [
                                'src',
                                'src'
                            ];
                            $desc = str_replace($dataImageSearch, $dataImgReplace, $desc);
                            $rules[$i]['content'] = $desc;
                        }

                        if ($rules[$i]['key'] == 'pro_thong_so_ky_thuat') {
                            $listThongSo = [];
                            if (!empty($result['custom_attributes'])) {
                                foreach ($result['custom_attributes'] as $thongSo) {
                                    $data = [
                                        $thongSo['label'] => $thongSo['value']
                                    ];
                                    array_push($listThongSo, $data);
                                }
                            }
                            $rules[$i]['content'] = $listThongSo;
                        }
                        if ($rules[$i]['key'] == 'pro_infor_shop') {
                            $result['vendor_info'] = isset($result['vendor_info'][0]) ? $result['vendor_info'][0] : [];
                            $idShop = isset($result['vendor_info']['id']) ? intval($result['vendor_info']['id']) : 0;
                            $nameShop = isset($result['vendor_info']['name']) ? $result['vendor_info']['name'] : "";
                            $slugNameShop = isset($result['vendor_info']['url_key']) ? $result['vendor_info']['url_key'] : "";
                            $urlShop = isset($result['vendor_info']['url']) ? $result['vendor_info']['url'] : $this->linkShopWeb . $idShop . '/' . $slugNameShop;
                            if ($idShop > 0 && !empty($nameShop) && !empty($slugNameShop)) {
                                $data = [
                                    'text' => $nameShop,
                                    'href' => $urlShop
                                ];
                                $rules[$i]['content'] = [
                                    $data
                                ];
                            }

                        }

                        if ($download == $this->isDownload) {
//                            if (in_array($rules[$i]['type'], [$this->src, $this->price])) {
//                                $temp[$rules[$i]['key']] = json_encode($rules[$i]['content']);
//                            } else {
//
//                            }
                            $temp[$rules[$i]['key']] = $rules[$i]['content'];
                            if (intval($cateId) > 0) {
                                $temp['cate_id'] = intval($cateId);
                            }
                        } else {
                            $temp[$i] = $rules[$i];
                        }
                    }

                    /**
                     * @set meta
                     */
                    /**
                     * @them meta
                     */
                    array_push($meta, [
                        'key' => 'description',
                        'name' => 'description',
                        'type' => 'text',
                        'content' => ""
                    ]);
                    array_push($meta, [
                        'key' => 'meta_title',
                        'name' => 'meta_title',
                        'type' => 'text',
                        'content' => $result['name']
                    ]);
                    $imageSeo = "";
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
                        'content' => ""
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
            $return['message'] = $exception->getMessage();
        }
        return $return;

    }
}