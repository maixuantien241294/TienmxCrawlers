<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2/28/2019
 * Time: 2:35 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerSpecForKeyword
{
    use BaseTrait;

    public function executeThongSo($contentHtml, $queryAll)
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {
            if (empty($queryAll['value']) || empty($queryAll['ten_thong_so']) || empty($queryAll['gia_tri_thong_so'])) {
                throw new \Exception('Không tồn tại luật lấy');
            }
            $rule = $queryAll['value'];
            $ruleTenThongSo = $queryAll['ten_thong_so'];
            $ruleGiaTriThongSo = $queryAll['gia_tri_thong_so'];
            $ruleHtml = $this->getRuleHtml($rule);
            if (empty($ruleHtml)) {
                throw new \Exception('Không tồn tại luật');
            }
            $results = [];
            for ($i = 0; $i < count($ruleHtml); $i++) {
                $check = $this->checkXpath($ruleHtml[$i]);

                if ($check === false) {
                    $newString = $this->parseDom($contentHtml, $ruleHtml[$i]);
                } else {
                    $newString = $this->parseXpth($contentHtml, $ruleHtml[$i]);
                }
                if (!empty($newString)) {
                    /**
                     * @lấy tên thông số kỹ thuật
                     */
                    $cTenThongSo = new CrawlerTenThongSo();
                    $tenThongSo = $cTenThongSo->crawler($newString, "", $ruleTenThongSo);
                    if (!empty($tenThongSo)) {
                        $results = array_merge($results, $tenThongSo);
                    }
                    /**
                     * @lấy giá trị thông số kỹ thuật
                     */
                    $crawlerSpecValue = new CrawlerSpecValueForKeyword();
                    $resValue = $crawlerSpecValue->crawler($newString, $ruleGiaTriThongSo);
                    if (!$resValue['error']) {
                        $results = array_merge($results, $resValue['data']);
                    }
                }
            }
            if (!empty($results)) {
                $return['message'] = MSG_SUCCESS;
                $return['error'] = false;
                $return['data'] = $results;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }


    public function parseDom($contentHtml, $rule)
    {
        $dom = HtmlDomParser::str_get_html($contentHtml);
        $element = $dom->find($rule);
        $html = "";
        if (count($element) > 0) {

            foreach ($element as $item) {
                $html .= $item->outertext();
            }
            $html = html_entity_decode($html);
        }
        return $html;
    }

    public function parseXpth($contentHtml, $rule)
    {
        $htmlString = "";
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);
        $nodelist = $xpath->evaluate($rule);
        if ($nodelist->length > 0) {
            foreach ($nodelist as $key => $item) {
                $htmlString = $html->saveHTML($item);
            }
        }
        return $htmlString;
    }

    public function excuteValue($contentHtml, $rule)
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {

        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }
}