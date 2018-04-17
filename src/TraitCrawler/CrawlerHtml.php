<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 7:04 PM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerHtml
{
    use BaseTrait;
    public $isDownload = 1; //crawler để download
    public $notDownload = 2;// crawler không đownload

    public function executeHtml($html, $xpath, $rule, $tagsSrc, $linkWebsite, $domain, $valueRemove, $valueRemoveXpath, $valueRemoveBlock,$download)
    {
        $htmlString = "";
        $ruleParse = $this->getRules($rule);
        $nodelist = $xpath->evaluate($ruleParse);
        foreach ($nodelist as $key => $item) {
            $htmlStringFirst = $html->saveHTML($item);
        }
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        @$domDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $htmlStringFirst);
        $xpathEnd = new \DOMXPath($domDocument);

        /**
         * @desc remove by xpath
         */
        if (!empty($valueRemoveXpath)) {
            $valueXpath = explode(',', $valueRemoveXpath);
            if (!empty($valueXpath)) {
                for ($i = 0; $i < count($valueXpath); $i++) {
                    $nodeValue = $xpathEnd->query($valueXpath[$i]);
                    if ($nodeValue->length > 0) {
                        foreach ($nodeValue as $value) {
                            $value->parentNode->removeChild($value);
                        }
                    }
                }
            }
        }
        /**
         * @desc lấy tất cả các ảnh theo tagsrc
         */
        $tagsSrc = empty($tagsSrc) ? 'src' : $tagsSrc;
        $tagsSrc = explode(',', $tagsSrc);
        for ($j = 0; $j < count($tagsSrc); $j++) {
            $listImg = $xpathEnd->query('//img');
            foreach ($listImg as $key => $item) {
                $oldImg = $listImg->item($key)->getAttribute($tagsSrc[$j]);

                if(!empty($oldImg)){
                    if($download == $this->isDownload){
                        $oneDot = explode('.', $oldImg);
                        $ext = end($oneDot);
                        $respone = $this->download($oldImg, 'content', $ext);
                        if ($respone['errors'] === false) {
                            $newImg = $respone['msg'];
                            $listImg->item($key)->setAttribute($tagsSrc[$j], $newImg);
                        }
                    }else{
                        $listImg->item($key)->setAttribute('src', $oldImg);
                    }
                }


            }
        }
        /*
         * @desc end download ảnh
         */
        $valueBlock = $domDocument->getElementsByTagName($valueRemoveBlock);
        if ($valueBlock->item(0)) {
            for ($i = $valueBlock->length; --$i >= 0;) {
                $href = $valueBlock->item($i);
                $href->parentNode->removeChild($href);
            }
        }

        $htmlString = $domDocument->saveHTML();

        $htmlString = htmlspecialchars_decode($this->removeValue($valueRemove, '', htmlspecialchars($htmlString)));
        // $trim_off_front = strpos($htmlString, '<body>') + 6;
        // $trim_off_end = (strrpos($htmlString, '</body>')) - strlen($htmlString);
        // $htmlString = substr($htmlString, $trim_off_front, $trim_off_end);
        return $htmlString;
    }
}