<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2/28/2019
 * Time: 2:53 PM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerSpecValueForKeyword
{
    use BaseTrait;

    public function crawler($contentHtml, $queryAll)
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {
            $result = [];
            $ruleHtml = $this->getRuleHtml($queryAll);
            if (empty($ruleHtml)) {
                throw new \Exception('Không tồn tại luật');
            }
            for ($i = 0; $i < count($ruleHtml); $i++) {
                $check = $this->checkXpath($ruleHtml[$i]);
                if ($check === false) {
                    $res = $this->parseDom($contentHtml, $ruleHtml[$i]);

                } else {
                    $res = $this->parseXpath($contentHtml, $ruleHtml[$i]);
                }
                if (!$res['error']) {
                    $result = $res['data'];
                }
            }
            if (!empty($result)) {
                $return['message'] = MSG_SUCCESS;
                $return['data'] = $result;
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
            $dom = HtmlDomParser::str_get_html($contentHtml);
            $element = $dom->find($rule);
            if (count($element) == 0) {
                throw new \Exception('Không tồn tại bản ghi');
            }
            $result = [];
            foreach ($element as $item) {

                $cardA = $item->find('a');

                if (!empty($item->text())) {
                    $text = $this->replace_text($item->text());
                    $arrlen = explode(' ', $text);
//                    if (count($arrlen) > 7) continue;
                    array_push($result, $text);
                }
                if (count($cardA) > 0) {
                    foreach ($cardA as $value) {
                        $textA = $this->replace_text($value->text());
                        $arrlen = explode(' ', $textA);
//                        if (count($arrlen) > 7) continue;
                        array_push($result, $textA);
                    }
                }
            }
            $result = array_unique($result);
            $listExport = [];
            foreach ($result as $key => $item) {
                $expKeyword = explode(',', $item);
                if (count($expKeyword) > 1) {
                    for ($i = 0; $i < count($expKeyword); $i++) {
                        array_push($listExport, trim($expKeyword[$i]));
                    }
                    unset($result[$key]);
                } else {
                    $result[$key] = preg_replace('/\(.*\)/U', '', $item);
                    $result[$key] = trim(str_replace(['/'], ' ', $result[$key]));
                    $removeNumber = preg_replace('/\d/', '', $result[$key]);
                    if (empty(trim($removeNumber))) {
                        unset($result[$key]);
                    }
                }
            }
            $result = array_merge($result, array_unique($listExport));
            $result = array_unique($result);
            if (!empty($result)) {
                $return['message'] = MSG_SUCCESS;
                $return['data'] = $result;
                $return['error'] = false;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }

    protected function removeQuote($string)
    {
        $string = trim($string);
        $string = str_replace("\'", "'", $string);
        $string = str_replace('"', "", $string);

        return $string;
    }

    public function parseXpath($contentHtml, $rule)
    {
        $return = ['message' => MSG_BAD_REQUEST, 'data' => [], 'error' => true];
        try {
            $result = [];
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
            $xpath = new \DOMXPath($html);
            $ruleParse = $this->getRules($rule);
            $nodelist = $xpath->query($ruleParse);
            if ($nodelist->length > 0) {
                for ($i = 0; $i < $nodelist->length; $i++) {
                    $value = $nodelist->item($i)->nodeValue;
                    $text = $this->replace_text($value);
                    array_push($result, $text);
                }
            }
            $result = array_unique($result);
            $listExport = [];
            foreach ($result as $key => $item) {
                $expKeyword = explode(',', $item);
                if (count($expKeyword) > 1) {
                    for ($i = 0; $i < count($expKeyword); $i++) {
                        array_push($listExport, trim($expKeyword[$i]));
                    }
                    unset($result[$key]);
                } else {
                    $result[$key] = preg_replace('/\(.*\)/U', '', $item);
                    $result[$key] = trim(str_replace(['/'], ' ', $result[$key]));
                    $removeNumber = preg_replace('/\d/', '', $result[$key]);
                    if (empty(trim($removeNumber))) {
                        unset($result[$key]);
                    }
                }
            }
            $result = array_merge($result, array_unique($listExport));
            $result = array_unique($result);
            if (!empty($result)) {
                $return['message'] = MSG_SUCCESS;
                $return['data'] = $result;
                $return['error'] = false;
            }
        } catch (\Exception $exception) {
            $return['message'] = $exception->getMessage();
        }
        return $return;
    }

}