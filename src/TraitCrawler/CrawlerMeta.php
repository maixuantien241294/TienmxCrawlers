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
    public function executeMeta($contentHtml, $linkWebsite, $domain){
        try{
            $listMeta = [];
            if (!empty($contentHtml)) {
                $html = new \DOMDocument();
                @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
                $metas= $html->getElementsByTagName('meta');
                
                if ($metas->length > 0) {
                    for ($i = 0; $i < $metas->length; $i++) {
                        $meta = $metas->item($i);
                        if($meta->getAttribute('name') == 'description'){
                            $description = $meta->getAttribute('content');
                            array_push($listMeta,[
                                'key'   =>  'description',
                                'name'  =>  'description',
                                'type'  =>  'text',
                                'content'   =>  $description
                            ]);

                        }
                        if($meta->getAttribute('name') == 'keywords'){
                            $keywords = $meta->getAttribute('content');
                            array_push($listMeta,[
                                'key'   =>  'keywords',
                                'name'  =>  'keywords',
                                'type'  =>  'text',
                                'content'   =>  $keywords
                            ]);

                        }
                        if($meta->getAttribute('property') == 'og:image'){
                            $image = $meta->getAttribute('content');
                            array_push($listMeta,[
                                'key'   =>  'image',
                                'name'  =>  'image seo',
                                'type'  =>  'src',
                                'content'   =>  [
                                    $image
                                ]
                            ]);

                        }
                    }
                }
            }
            return $listMeta;
        }catch (\Exception $exception){
            dd($exception->getMessage());
        }
    }
}