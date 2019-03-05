<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 6:56 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


trait BaseTrait
{
    public $saveFolder = "";
    public $wget = "";
    public $urlFile = "";
    public $regexReplaceContent = ['*', '-', '_', '#', ':', '.', '+', "\'", '"'];

    public function getRules($rule)
    {
        $ruleParse = $rule;
        $ruleData = explode(',', $rule);

        if (count($ruleData) > 0) {
            $ruleParse = $ruleData[count($ruleData) - 1];
        }
        return $ruleParse;
    }

    public function is_image($path)
    {
        if (!empty($path)) {
            $a = getimagesize($path);
            if (isset($a[2])) {
                $image_type = $a[2];
                if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getRuleHtml($rule)
    {
        $listRule = [];

        $ruleData = explode('|', $rule);
        if (count($ruleData) > 0) {
            for ($i = 0; $i < count($ruleData); $i++) {
                $ruleData[$i] = trim($ruleData[$i]);
                if (!empty($ruleData[$i])) {
                    $newRule = $this->getRules(trim($ruleData[$i]));
                    array_push($listRule, $newRule);
                }
            }
        }
        return $listRule;
    }

    public function removeValue($search, $replace, $string)
    {

        $search = explode('|', $search);
        $result = $string;
        if (is_array($search) || is_object($search) || !empty($search)) {
            foreach ($search as $value_search) {
                $value_search = trim($value_search);
                $lower = mb_strtolower($value_search);
                $uper = mb_strtoupper($value_search);
                $upperFirst = ucfirst(strtolower($value_search));
                $uc = ucwords(strtolower($value_search));
                $arrayReplace = [$lower, $uper, $upperFirst, $uc];
                for ($i = 0; $i < count($arrayReplace); $i++) {
                    $result = str_replace($arrayReplace[$i], $replace, $result);
                }

            }
        }
        return $result;
    }

    public function download($src, $outputFolder, $ext = 'jpg')
    {
        $return = ['errors' => true, 'msg' => ""];
        try {
            $now = date("Y/m/d/H");
            $path = $outputFolder . '/' . $now . '/';
            $Fullpath = $this->saveFolder . $path;

            @mkdir($Fullpath, 0775, true);
            $nameFile = $this->getAlias($outputFolder) . '_' . uniqid() . '.' . $ext;
            $cmd = $this->wget . ' "' . $src . '"' . ' -O ' . '"' . $Fullpath . $nameFile . '"';
            exec($cmd);
            if (file_exists($Fullpath . $nameFile) && filesize($Fullpath . $nameFile) > 0) {
                $return['errors'] = false;
                $return['msg'] = $this->urlFile . '/' . $path . $nameFile;
            }
        } catch (\Exception $exception) {
            $return['msg'] = $exception->getMessage();
        }
        return $return;

    }

    public function callApi($url, $data)
    {
        try {

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    public function addJsContent($data = array(), $content)
    {
        $link = isset($data['link_website']) ? $data['link_website'] : $data['link'];
        $oldLink = isset($data['link_website']) ? $data['link_website'] : $data['link'];
        $link = $this->getUrl($link);
        $explodeLink = explode('/', $link);
        if (count($explodeLink) === 4) {
            $link = substr($link, 0, strlen($link) - 1);
        }

        $linkCrawler = isset($data['link']) ? $data['link'] : "";
        /**
         * @desc replace link css
         */
        $header = '<base href="' . $link . '" target="_blank">';
        $header .= '<base href="' . $link . '/' . '" target="_blank">';
        if (!empty($linkCrawler)) {
            $linkCrawler = $this->getUrl($linkCrawler);
            $header = '<base href="' . $linkCrawler . '" target="_blank">';
            $header .= '<base href="' . $linkCrawler . '/' . '" target="_blank">';
        }
        $content = str_replace('<head>', '<head>' . $header, $content);
//        preg_match('%<(head)[^>]*>%s', $content, $matches);
//        if (count($matches) > 0) {
//            $content = preg_replace('%<(head)[^>]*>%s', '<head>' . $header, $content);
//        } else {
//            $content = str_replace('<head>', '<head>' . $header, $content);
//        }

        $dataHeader = '<link rel="stylesheet" type="text/css" href="' . env('APP_DOMAIN', '') . 'getruler/inject.css?=v' . VERSION . '">';
        $dataHeader .= '<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>';
        $dataHeader .= '<script>if(typeof $ != \'undefined\'){var _$ = $;}else{var _$ = false;}</script>';
        $dataHeader .= '<script>if(typeof jQuery != \'undefined\'){var _jQuery = jQuery.noConflict();}else{var _jQuery = false;}</script>';
        $dataHeader .= '<script>var ACjQuery = jQuery.noConflict();</script>';
        $dataHeader .= '<script type="text/javascript" src="' . env('APP_DOMAIN', '') . 'getruler/inject.js?=v' . VERSION . '"></script>';
        $dataHeader .= '<script type="text/javascript" src="' . env('APP_DOMAIN', '') . 'getruler/_getDomPath.js?=v' . VERSION . '"></script>';
        $dataHeader .= '<script>if(_jQuery)jQuery = jQuery;</script><script>if(_$)$ = $;</script>';
        $content = str_replace('</body>', $dataHeader . '</body>', $content);

        return $content;
    }

    public function stripAccents($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        $str = preg_replace('/[^\w\d_ -]/si', '', $str);
        return $str;
    }

    public function getAlias($str)
    {
        $str = $this->stripAccents($str);
//        $str = self::removeNonAlphaNumberic($str, " ");
        $str = preg_replace('/[\s]+/', " ", $str); // remove multiple space --> one space
        $str = str_replace(' ', '-', $str);
        $str = mb_strtolower($str);
        return $str;
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

    public function validImage($file)
    {
        $size = getimagesize($file);
        return (strtolower(substr($size['mime'], 0, 5)) == 'image' ? true : false);
    }

    public function getUrl($link)
    {
        $url = $link;
        $result = parse_url($link);
        if (isset($result['scheme']) && isset($result['host'])) {
            $url = $result['scheme'] . '://' . $result['host'];
        }
        return $url;
    }

    protected function __check_url($href, $domain, $linkWeb)
    {

        if (!preg_match('/' . $domain . '/', $href, $match)
            && empty(parse_url($href, PHP_URL_HOST)) && !empty($href)) {
            /**
             * @desc : Remove ../ của href
             */

            $testHref = substr($href, '0', 2);
            $href = ($testHref != "..") ? $href : substr($href, 2, strlen($href));
            /**
             * @desc : Test
             */
            $testElement = substr($href, '0', 1);
            if ($testElement != '/') {
                $href = $linkWeb . '/' . $href;
            } else {
                $href = $linkWeb . $href;
            }
        } else {

            $parse = parse_url($href);
            if (!isset($parse['host'])) {

                $testElement = substr($href, '0', 1);
                if ($testElement != '/') {
                    $href = $linkWeb . '/' . $href;
                } else {
                    $href = $linkWeb . $href;
                }
            } else {
                $scheme = isset($parse['scheme']) ? $parse['scheme'] : "";
                $host = isset($parse['host']) ? $parse['host'] : "";
                $path = isset($parse['path']) ? $parse['path'] : "";
                $query = isset($parse['query']) ? $parse['query'] : "";

                if (!empty($path)) {
                    $testPath = substr($path, '0', 2);
                    $path = ($testPath != "..") ? $path : substr($path, 2, strlen($path));
                    if (!empty($query)) {
                        $path = $path . '?' . $query;
                    }
                }

                if (!empty($scheme)) {
                    $href = $scheme . '://' . $host . $path;
                } else {
                    $href = '//' . $host . $path;
                }
                if (in_array($domain, ['robins.vn', '131mobile.vn'])) {
                    if ($domain == 'robins.vn') {
                        $regexNew = '#http://static-catalog.robins.vn/[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#i';
                    } elseif ($domain == '131mobile.vn') {
                        $regexNew = '#http://131mobile.vn/uploads/[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#i';
                    }
                    preg_match($regexNew, $href, $matchnew);
                    if (isset($matchnew[0])) {
                        preg_match('/(jpg|jpeg|png|JPG|PNG|JPEG|GIF|gif)/', $matchnew[0], $matchImg);
                        if (!empty($matchImg)) {
                            $href = $matchnew[0];
                        }
                    }

                }
            }
            if (!preg_match("~^(?:f|ht)tps?://~i", $href)) {
                $href = "http:" . $href;
            }

        }
        return $href;
    }

    public function callApiGet($headers = array(), $url = "")
    {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://www.sendo.vn/m/wap_v2/full/san-pham/ao-da-nam-xin-4510730?source_block_id=listing_products&source_info=desktop2_60_1534409830115_437985205_0_default_20_1_2&source_page_id=cate3_default_listing_desc",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "id=50132",
                CURLOPT_HTTPHEADER => array(//                    "If-none-match-: 55b03-987390ecdcf5cfd8f45c6dcbc4599dda"
                ),
            ));

            $response = curl_exec($curl);
            dd($response);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                echo $response;
            }
            curl_close($curl);
            return $response;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function keyword_remove()
    {
        $reject_keyword = array('mua', 'rẻ', 'giá', 'bán', 'nhưng', 'xách', ' ở ', 'triệu',
            'nhất', 'vào', 'vô', 'phim', 'tốt', 'mới', 'của', 'là gì', 'trả', 'góp',
            'yêu', 'trâu', 'khủng', 'động', 'full', 'không', 'like', 'cổ', 'hà', 'nội',
            'mọi', 'vui', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021',
            'mẹ', 'nguyen', 'lazada', 'tiki', 'vatgia', 'sendo', 'shopee', 'quả', 'quốc', 'quân', 'quay',
            'tầm', 'tai thỏ', 'tai tho', 'tràn', 'thegioi', 'fpt', 'sim', 'treo', 'xuống', 'mất', 'chai', 'tháng', 'tại',
            'ý', 'quận', 'tỉnh', 'lừa', 'bị', 'đơ', 'ngoai', 'ngoài', 'gập', 'nào', 'hãng', 'review', 'xach', 'viễn', 'moi',
            'nhận', 'chống', 'bts', 'nhái', 'nhai', 'tphcm', 'gia', 'tinhte', 'yếu', 'facebook', 'rồi', 'yếu', 'đà nẵng',
            'hải dương', 'hà nội', 'chiết khấu', 'chạy chậm', 'chập chờn', 'vỡ kính', 'bắt được', 'facebook', 'tài khoản', 'với', 'khong duoc'
        , 'không được', 'lên', 'san xuat', 'sản xuất', 'gì', 'làm gì', 'trang chủ');
        return $reject_keyword;
    }

    public function replace_text($textA)
    {
        $textA = trim(mb_strtolower($textA, 'UTF-8'));
        $textA = str_replace('*', ' ', $textA);
        $textA = str_replace('-', ' ', $textA);
        $textA = str_replace('#', ' ', $textA);
        $textA = str_replace('_', ' ', $textA);
        $textA = str_replace('&', ' ', $textA);
        $textA = str_replace('%', ' ', $textA);
        $textA = str_replace(':', ' ', $textA);
        $textA = str_replace('.', ' ', $textA);
        $textA = str_replace('+', ' ', $textA);
        $textA = str_replace("\'", ' ', $textA);
        $textA = str_replace('"', ' ', $textA);
        $textA = str_replace('/', ' ', $textA);
        $textA = preg_replace('/\(.*\)/U', '', $textA);
        $textA = preg_replace('/\s\s+/', ' ', $textA);
        $textA = preg_replace('/\[.*\]/U', '', $textA);
        $textA = preg_replace('/\{.*\}/U', '', $textA);

        return trim($textA);
    }
}