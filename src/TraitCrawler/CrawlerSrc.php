<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:05 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerSrc
{
    use BaseTrait;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    /**
     * @param $xpath
     * @param $rule
     * @param $tagsSrc
     * @param $linkWebsite
     * @param $domain
     * @param $valueRemove
     * @return array
     */
    public function executeSrc($xpath, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $download)
    {
        /**
         * @remove '/' cuối của $linkWebsite
         */
        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $explodeLink = explode('/', $linkWebsite);
        if (count($explodeLink) === 4) {
            $linkWebsite = substr($linkWebsite, 0, strlen($linkWebsite) - 1);
        }
        $htmlString = [];
        $ruleParse = $this->getRules($rule);
        $tagsSrc = explode(',', $tagsSrc);
        foreach ($tagsSrc as $item) {
            $ruleImg = $ruleParse . '/@' . $item;
            $nodelist = $xpath->query($ruleImg);
            if ($nodelist->length > 0) {

                foreach ($nodelist as $key => $node) {
                    $image = $nodelist->item($key)->value;

                    if (!preg_match('/' . $domain . '/', $image, $match)
                        && empty(parse_url($image, PHP_URL_HOST)) && !empty($image)) {
                        $image = $linkWebsite . $image;
                    }
                    array_push($htmlString, $image);
                }
                break;
            }
        }
        return $htmlString;
    }
}