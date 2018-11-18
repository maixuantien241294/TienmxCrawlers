<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 10/14/2018
 * Time: 5:58 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerGiaoTrinh
{
    use BaseTrait;
    protected $phanTuDauMXT = '<phanTuDauMXT>';

    public function executeGiaoTrinh($contentHtml, $domain, $queryAll)
    {
        try {

            $results = [];
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
            $xpath = new \DOMXPath($html);
            $ruleHtml = $queryAll['value'];
            $nodelist = $xpath->evaluate($ruleHtml);
            $htmlString = "";
            if ($nodelist->length > 0) {
                foreach ($nodelist as $key => $item) {
                    $htmlString = $html->saveHTML($item);
                }
            }
            $phanTuDau = $queryAll['phan_tu_dau'];
            $phanTuCuoi = $queryAll['phan_tu_cuoi'];
            $htmlString = preg_replace('/<div class=\"' . $phanTuDau . '\">/', $this->phanTuDauMXT, $htmlString);

            if (!empty($htmlString)) {
                $giaoTrinhs = explode($this->phanTuDauMXT, $htmlString);

                $dataGiaoTrinh = [];
                if (!empty($giaoTrinhs)) {
                    foreach ($giaoTrinhs as $key => $giaoTrinh) {
                        if ($key > 0) {
                            $regex = '#([^<]*)' . $phanTuCuoi . '#';
                            preg_match($regex, $giaoTrinh, $match);
                            if (count($match) > 0) {
                                $data = $this->crawlerTenBai($giaoTrinh, $queryAll);
                                $list = [
                                    'name' => isset($match[1]) ? trim($match[1]) : "",
                                    'list' => $data
                                ];
                                array_push($results, $list);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        return $results;
    }

    public function crawlerTenBai($contentHtml, $query)
    {
        $return = [];
        $ruleTenBai = $query['ten_bai'];
        $ruleThoiGian = $query['thoi_gian'];
        $html = new \DOMDocument();
        @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
        $xpath = new \DOMXPath($html);
        $nodeTenBai = $xpath->evaluate($ruleTenBai);

        $listTenBai = [];
        $listThoiGian = [];
        if ($nodeTenBai->length > 0) {
            for ($i = 0; $i < $nodeTenBai->length; $i++) {
                $value = $nodeTenBai->item($i)->nodeValue;
                if (!empty($value)) {
                    array_push($listTenBai, trim($value));
                }
            }
        }
        $nodeThoiGian = $xpath->evaluate($ruleThoiGian);
        if ($nodeThoiGian->length > 0) {
            for ($i = 0; $i < $nodeThoiGian->length; $i++) {
                $value = $nodeThoiGian->item($i)->nodeValue;
                if (!empty($value)) {
                    array_push($listThoiGian, trim($value));
                }
            }
        }

        if (!empty($listTenBai)) {
            for ($j = 0; $j < count($listTenBai); $j++) {
                $data = [
                    'ten_bai' => trim($listTenBai[$j]),
                    'thoi_gian' => isset($listThoiGian[$j]) ? trim($listThoiGian[$j]) : ""
                ];
                array_push($return, $data);
            }
        }
        return $return;
    }
}