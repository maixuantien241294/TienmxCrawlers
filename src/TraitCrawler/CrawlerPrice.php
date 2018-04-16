<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:04 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerPrice
{
    use BaseTrait;

    public function executePrice($xpath, $rule, $valueRemove)
    {
        $htmlString = "";
        $ruleParse = $this->getRules($rule);
        $nodelist = $xpath->query($ruleParse);
        if ($nodelist->length > 0) {
            $htmlString = trim($nodelist->item(0)->nodeValue);
        }
        //lấy tất cả là số trong chuỗi tring;

        $price = [];
        /**
         * @desc => Kiểm tra nếu có dấu gạch ngang `-`
         */
        $expPrice = explode('-', $htmlString);
        if (count($expPrice) >= 1) {
            foreach ($expPrice as $item) {
                $priceItem = "";
                if (preg_match_all('/\d+/', $item, $matches)) {
                    $matches = $matches[0];
                    foreach ($matches as $value) {
                        $priceItem .= $value;
                    }
                }
                array_push($price, $priceItem);
            }
        }
        return $price;
    }
}