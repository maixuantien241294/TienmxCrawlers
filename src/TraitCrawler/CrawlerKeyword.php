<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2/28/2019
 * Time: 9:58 AM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerKeyword
{
    use BaseTrait, CrawlerTypeTrait;
    protected $keyword = 'pro_keyword';
    protected $thong_so_ky_thuat = 'pro_thong_so_ky_thuat';
    protected $thong_so_ky_thuat_ngan = 'pro_thong_so_ky_thuat_ngan';
    public $regexReplace = ['*', '-', '_', '#', ':', '.'];

    public function __construct($wget, $saveFolder, $urlFile)
    {
        $this->wget = $wget;
        $this->saveFolder = $saveFolder;
        $this->urlFile = $urlFile;
    }

    public function getData($contentHtml, $cateId = "", $tagsSrc, $rules, $linkWebsite, $domain, $download, $replaceImg = [], $webRuleImgSpec, $showArray = 0, $linkCrawler = "")
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {
            $results = [];
            $rule_keyword = [];
            $rule_thong_so_ky_thuat = [];
            $rule_thong_so_ky_thuat_ngan = [];
            $result_rules = [];
            $result_spec = [];
            foreach ($rules as $key => $item) {
                if ($item['key'] == $this->keyword) {
                    $rule_keyword = $item;
                }
                if ($item['key'] == $this->thong_so_ky_thuat) {
                    $rule_thong_so_ky_thuat = $item;
                }
                if ($item['key'] == $this->thong_so_ky_thuat_ngan) {
                    $rule_thong_so_ky_thuat_ngan = $item;
                }
            }
            if (!isset($rule_keyword['value']) && (isset($rule_keyword['value']) && empty($rule_keyword['value']))) {
                throw new \Exception('Không tồn tại luật của keyword');
            }

            $rule_keyword = $rule_keyword['value'];
            $respone = $this->executeKeyword($contentHtml, $rule_keyword);
            if (!$respone['error']) {
                $result_rules = array_merge($result_rules, $respone['data']);
                $listExport = [];
                foreach ($result_rules as $key => $item) {
                    $expKeyword = explode(',', $item);
                    if (count($expKeyword) > 1) {
                        for ($i = 0; $i < count($expKeyword); $i++) {
                            array_push($listExport, trim($expKeyword[$i]));
                        }
                        unset($result_rules[$key]);
                    } else {
                        $result_rules[$key] = preg_replace('/\(.*\)/U', '', $item);
                        $result_rules[$key] = trim(str_replace(['/'], ' ', $result_rules[$key]));
                        $removeNumber = preg_replace('/\d/', '', $result_rules[$key]);
                        if (empty(trim($removeNumber))) {
                            unset($result_rules[$key]);
                        }
                    }
                }
                $result_rules = array_merge($result_rules, array_unique($listExport));
                $result_rules = array_unique($result_rules);
            }
            /**
             * @lấy dữ liệu từ thông số kỹ thuật ngắn
             */
            $crawlerSpec = new CrawlerSpecForKeyword();
            $responeSpecMin = $crawlerSpec->executeThongSo($contentHtml, $rule_thong_so_ky_thuat_ngan);
            if (!$responeSpecMin['error']) {
                $result_spec = array_merge($result_spec, $responeSpecMin['data']);
            }
            /**
             * @lấy dữ liệu từ thông số kỹ thuật full
             */
            $responeSpec = $crawlerSpec->executeThongSo($contentHtml, $rule_thong_so_ky_thuat);
            if (!$responeSpec['error']) {
                $result_spec = array_merge($result_spec, $responeSpec['data']);
            }
            $result_spec = array_unique($result_spec);
            /**
             * @neu lon hon 7 ky tu thi xoa
             */
            $new_result_rules = [];
            $new_result_spec = [];
            if (!empty($result_rules)) {
                foreach ($result_rules as $item) {
                    $arrlen = explode(' ', $item);
                    if (count($arrlen) > 7) continue;
                    array_push($new_result_rules, $item);
                }
            }
            if (!empty($result_spec)) {
                foreach ($result_spec as $item) {
                    $arrlen = explode(' ', $item);
                    if (count($arrlen) > 7) continue;
                    array_push($new_result_spec, $item);
                }
            }
            $results = [
                'rules' => $new_result_rules,
                'spec' => $new_result_spec
            ];

            if (!empty($results)) {
                $return['message'] = MSG_SUCCESS;
                $return['data'] = $results;
                $return['error'] = false;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }

    public function executeKeyword($contentHtml, $rule)
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {
            $ruleHtml = $this->getRuleHtml($rule);
            $listKeyword = [];
            if (empty($ruleHtml)) {
                throw new \Exception('Không tồn tại luật');
            }
            for ($i = 0; $i < count($ruleHtml); $i++) {
                $ruleHtml[$i] = trim($ruleHtml[$i]);
                if (!empty($ruleHtml[$i])) {
                    $check = $this->checkXpath($ruleHtml[$i]);
                    if ($check === false) {
                        $respone = $this->parseDom($contentHtml, $ruleHtml[$i]);
                    } else {
                        $respone = $this->parseXpath($contentHtml, $ruleHtml[$i]);
                    }
                    if (!$respone['error']) {
                        $listKeyword = array_merge($listKeyword, $respone['data']);
                    }
                }
            }
            if (!empty($listKeyword)) {
                $return['data'] = $listKeyword;
                $return['error'] = false;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }

    public function parseDom($contentHtml, $rule)
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {
            $keyword = [];
            $dom = HtmlDomParser::str_get_html($contentHtml);
            $element = $dom->find($rule);
            $html = "";
            if (count($element) == 0) {
                throw new \Exception('Không có phần tư');
            }
            foreach ($element as $item) {
                $html .= $item->outertext();
            }
            $html = html_entity_decode($html);
            \Log::info($html, ['content' => $rule]);
            /**
             * @lấy tất cả từ có trong thẻ a
             */
            $newDom = HtmlDomParser::str_get_html($html);
            $elementNew = $newDom->find('a');
            if (count($elementNew) == 0) {
                throw new \Exception('Không có phần tư');
            }
            foreach ($elementNew as $item) {
                if (!empty($item->text())) {
                    $text = $this->replace_text($item->text());
                    $arrlen = explode(' ', $text);
//                    if (count($arrlen) > 7) continue;
                    array_push($keyword, $text);
                }
            }
            if (!empty($keyword)) {
                \Log::info($keyword, ['content' => $rule]);
                $return['message'] = MSG_SUCCESS;
                $return['data'] = $keyword;
                $return['error'] = false;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }

    public function parseXpath($contentHtml, $rule)
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
            $xpath = new \DOMXPath($html);
            $ruleParse = $this->getRules($rule);
            $nodelist = $xpath->query($ruleParse);
            $keyword = [];
            $htmlStringFirst = "";
            if ($nodelist->length > 0) {
                for ($n = 0; $n < $nodelist->length; $n++) {
                    $htmlStringFirst = $htmlStringFirst . $html->saveHTML($nodelist->item($n));
                }
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                @$domDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $htmlStringFirst);
                $xpathEnd = new \DOMXPath($domDocument);
                $elementNew = $xpathEnd->query('//a');
                if ($elementNew->length == 0) {
                    throw new \Exception('Không có phần tư');
                }
                for ($i = 0; $i < $elementNew->length; $i++) {
                    $text = trim($elementNew->item($i)->nodeValue);
                    $text = $this->replace_text($text);
                    $arrlen = explode(' ', $text);
//                    if (count($arrlen) > 7) continue;
                    array_push($keyword, $text);
                }
                if (!empty($keyword)) {
                    \Log::info($keyword, ['content' => $rule]);
                    $return['message'] = MSG_SUCCESS;
                    $return['data'] = $keyword;
                    $return['error'] = false;
                }
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }
}