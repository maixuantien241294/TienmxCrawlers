<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 6/15/2018
 * Time: 9:48 AM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerThongSoKyThuat
{
    use BaseTrait;

    public function executeThongSo($contentHtml, $domain, $queryAll)
    {
        try {
            $temp = [];
            if (!empty($queryAll['value']) || !empty($queryAll['ten_thong_so']) || !empty($queryAll['gia_tri_thong_so'])) {
                $rule = $queryAll['value'];
                $ruleTenThongSo = $queryAll['ten_thong_so'];
                $ruleGiaTriThongSo = $queryAll['gia_tri_thong_so'];
                $ruleHtml = $this->getRuleHtml($rule);
                if (!empty($ruleHtml)) {
                    for ($i = 0; $i < count($ruleHtml); $i++) {
                        $check = $this->checkXpath($ruleHtml[$i]);
                        $listThongSo = [];
                        if ($check === false) {
                            $newString = $this->parseDom($contentHtml, $ruleHtml[$i]);
                        } else {
                            $newString = $this->parseXpth($contentHtml, $ruleHtml[$i]);
                        }
                        /**
                         * @lấy các phần tử con
                         */
                        if (!empty($newString)) {
                            $cGiaTriThongSo = new CrawlerGiaTriThongSoKyThuat();
                            $giaTriThongSo = $cGiaTriThongSo->crawler($newString,$domain, $ruleGiaTriThongSo);
                            $cTenThongSo = new CrawlerTenThongSo();
                            $tenThongSo = $cTenThongSo->crawler($newString,$domain, $ruleTenThongSo);

                            if (!empty($tenThongSo) && !empty($giaTriThongSo)) {
                                for ($i = 0; $i < count($tenThongSo); $i++) {
                                    $data = [
                                        $tenThongSo[$i] => isset($giaTriThongSo[$i]) ? $giaTriThongSo[$i] : ""
                                    ];
                                    array_push($listThongSo, $data);
                                }
                            }
                        }
                        if (!empty($listThongSo)) {
                            foreach ($listThongSo as $item) {
                                array_push($temp, $item);
                            }
                        }
                    }
                }
            }
            return $temp;
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
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
}