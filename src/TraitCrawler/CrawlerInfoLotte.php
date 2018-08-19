<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 8/16/2018
 * Time: 5:09 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerInfoLotte
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
    public $domain = 'sendo.vn';
    public $api = 'https://www.lotte.vn/rest/V1/lotte_product/details';
    public $mode = 100000;
    public $linkwebsite = 'https://www.lotte.vn/';
    public $linkShopWeb = 'https://www.lotte.vn/seller/';
//    public $apiShop =''
    public $header = [];

    public function crawler($rules, $idPro,  $link, $domain, $linkWebsite, $cateId = 0, $download = 2)
    {
        $return = ['error' => true, 'message' => "lỗi hệ thống", 'content' => ""];
        $meta = [];
        $temp = [];
        $linkApi = "";
        try {
            $exp = explode('product/', $link);

            $id = 0;
            if(intval($idPro) > 0){
                $linkApi = $this->api . '?id=' . $idPro . '&isDetailPage=1';
            }else{
                if (!empty($exp) && isset($exp[1])) {
                    $expId = explode('/', $exp[1]);
                    if (!empty($expId)) {
                        $id = isset($expId[0]) ? intval($expId[0]) : 0;
                        $linkApi = $this->api . '?id=' . $id . '&isDetailPage=1';
                    }
                } else {
                    $exp1 = explode('/', $link);
                    $expIdEnd = end($exp1);
                    $expDot = explode('.', $expIdEnd);
                    if (!empty($expDot) && isset($expDot[0])) {
                        $id = isset($expDot[0]) ? intval($expDot[0]) : 0;
                        $linkApi = $this->api . '?id=' . $id . '&isDetailPage=1';
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
                            if (isset($result['media_gallery_entries'][0]) && !empty($result['media_gallery_entries'][0])) {
                                $testElement = substr($result['media_gallery_entries'][0]['file'], '0', 1);
                                if ($testElement != '/') {
                                    $href = $this->cdn . '/' . $result['media_gallery_entries'][0]['file'];
                                } else {
                                    $href = $this->cdn . $result['media_gallery_entries'][0]['file'];
                                }
                                $rules[$i]['content'] = [

                                    urlencode($href)
                                ];
                            }
                        }
                        if ($rules[$i]['key'] == 'pro_price') {
                            if (!empty($result['extension_attributes']) && isset($result['extension_attributes']['price'])) {
                                $rules[$i]['content'] = [
                                    doubleval($result['extension_attributes']['price'])
                                ];
                            }
                        }
                        if ($rules[$i]['key'] == 'pro_price_promotion') {
                            if (!empty($result['extension_attributes']) && isset($result['extension_attributes']['final_price'])) {
                                $rules[$i]['content'] = [
                                    doubleval($result['extension_attributes']['final_price'])
                                ];
                            }
                        }

                        if ($rules[$i]['key'] == 'pro_list_image') {
                            $listImg = [];
                            if (!empty($result['media_gallery_entries'])) {
                                foreach ($result['media_gallery_entries'] as $media) {
                                    $testElementData = substr( $media['file'], '0', 1);
                                    if ($testElementData != '/') {
                                        $hrefData = $this->cdn . '/' .  $media['file'];
                                    } else {
                                        $hrefData = $this->cdn .  $media['file'];
                                    }
                                    array_push($listImg, urlencode($hrefData));
                                }
                            }
                            $rules[$i]['content'] = $listImg;
                        }

                        if ($rules[$i]['key'] == 'pro_description') {
                            $desc = $result['extension_attributes']['description'];
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
                            if (!empty($result['extension_attributes']['additional'])) {
                                foreach ($result['extension_attributes']['additional'] as $thongSo) {
                                    $data = [
                                        $thongSo['label'] => $thongSo['value']
                                    ];
                                    array_push($listThongSo, $data);
                                }
                            }
                            $rules[$i]['content'] = $listThongSo;
                        }
                        if ($rules[$i]['key'] == 'pro_infor_shop') {
                            $idShop = isset($result['extension_attributes']['vendor_id']) ? intval($result['extension_attributes']['vendor_id']) : 0;
                            $nameShop = isset($result['extension_attributes']['vendor_name']) ? $result['extension_attributes']['vendor_name'] : "";
                            $slugNameShop = isset($result['extension_attributes']['vendor_slug']) ? $result['extension_attributes']['vendor_slug'] : "";
                            if ($idShop > 0 && !empty($nameShop) && !empty($slugNameShop)) {
                                $data = [
                                    'text' => $nameShop,
                                    'href' => $this->linkShopWeb . $idShop . '/' . $slugNameShop
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
                    $metaDesc = "";
                    if (isset($result['custom_attributes'][4])) {
                        foreach ($result['custom_attributes'] as $custom) {
                            if (isset($custom['attribute_code']) && $custom['attribute_code'] == 'meta_description') {
                                $metaDesc = $custom['value'];
                                break;
                            }
                        }

                    }
                    array_push($meta, [
                        'key' => 'description',
                        'name' => 'description',
                        'type' => 'text',
                        'content' => $metaDesc
                    ]);
                    array_push($meta, [
                        'key' => 'meta_title',
                        'name' => 'meta_title',
                        'type' => 'text',
                        'content' => $result['name']
                    ]);
                    $imageSeo = "";
                    if (isset($result['extension_attributes']['image'])) {
                        $imageSeo = $this->cdn . $result['extension_attributes']['image'];
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