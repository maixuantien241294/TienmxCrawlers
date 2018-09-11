<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 6/14/2018
 * Time: 9:42 AM
 */

namespace Tienmx\Crawler\TraitCrawler;

use Sunra\PhpSimple\HtmlDomParser;

class CrawlerMeta
{
    use BaseTrait;

    public function executeMeta($contentHtml, $linkWebsite, $domain)
    {
        try {
            $listMeta = [];
            if (!empty($contentHtml)) {
                $html = new \DOMDocument();
                @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
                $metas = $html->getElementsByTagName('meta');

                if ($metas->length > 0) {
                    for ($i = 0;
                         $i < $metas->length; $i++) {
                        $meta = $metas->item($i);
                        if ($meta->getAttribute('name') == 'description') {
                            $description = $meta->getAttribute('content');
//                            $description = $this->removeValue($domain, 'Muazi.vn', $description);
                            array_push($listMeta, [
                                'key' => 'description',
                                'name' => 'description',
                                'type' => 'text',
                                'content' => $description
                            ]);

                        }
                        if ($meta->getAttribute('name') == 'keywords') {
                            $keywords = $meta->getAttribute('content');
//                            $keywords = $this->removeValue($domain, 'Muazi.vn', $keywords);
                            array_push($listMeta, [
                                'key' => 'keywords',
                                'name' => 'keywords',
                                'type' => 'text',
                                'content' => $keywords
                            ]);

                        }
                        if ($meta->getAttribute('property') == 'og:image') {
                            $image = $meta->getAttribute('content');
                            $linkWebsite = $this->getUrl($linkWebsite);
                            if (!empty($image)) {
                                if ($domain == 'phucanh.vn') {
                                    $expl = explode('/media', $image);
                                    if (!empty($expl)) {
                                        if (isset($expl[1])) {
                                            $image = 'https://www.phucanh.vn/media' . $expl[1];
                                        }else{
                                             $image = "";
                                        }
                                    }
                                }
                                if(!empty($image)){
                                    $image = $this->__check_url($image, $domain, $linkWebsite);
                                }
                                
                                // $checkImage = $this->is_image($image);
                                // if ($checkImage == false) {
                                //     $image = "";
                                // }
                            }
                            
                            // if (!empty($image)) {
                            //     if ($domain == 'phucanh.vn') {
                            //         $expl = explode('/media', $image);
                            //         if (!empty($expl)) {
                            //             $image = 'https://www.phucanh.vn/media' . $expl[1];
                            //         }
                            //     }
                            //     $image = $this->__check_url($image, $domain, $linkWebsite);
                            // }
                            array_push($listMeta, [
                                'key' => 'image_seo',
                                'name' => 'image seo',
                                'type' => 'src',
                                'content' => [
                                    $image
                                ]
                            ]);

                        }
                    }
                }

                $title = $html->getElementsByTagName('title');
                if ($title->length > 0) {
                    $title = $title->item(0);
                    $nameTitle = $title->nodeValue;
                    if(!empty($nameTitle)){
//                        $nameTitle = $this->removeValue($domain, 'Muazi.vn', $nameTitle);
                        array_push($listMeta, [
                            'key' => 'meta_title',
                            'name' => 'meta_title',
                            'type' => 'text',
                            'content' => $nameTitle
                        ]);
                    }
                }
            }
            return $listMeta;
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
    }
}