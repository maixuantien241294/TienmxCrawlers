<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 5/31/2018
 * Time: 9:39 AM
 */

namespace Tienmx\Crawler\TraitCrawler;


class CrawlerLogo
{
    use BaseTrait;

    public function executeLogo($contentHtml, $linkWebsite, $domain)
    {
        $fevicon = '';
        if (!empty($contentHtml)) {
            $html = new \DOMDocument();
            @$html->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $contentHtml);
            $links = $html->getElementsByTagName('link');
            if ($links->length > 0) {
                for ($i = 0; $i < $links->length; $i++) {
                    $link = $links->item($i);
                    if ($link->getAttribute('rel') == 'icon' || $link->getAttribute('rel') == "Shortcut Icon" || $link->getAttribute('rel') == "shortcut icon") {
                        $fevicon = $link->getAttribute('href');
                    }
                }
            }
        }
        $linkWebsite = $this->getUrl($linkWebsite);
        if(!empty($fevicon)){
            $fevicon = $this->__check_url($fevicon,$domain,$linkWebsite);
        }
        return $fevicon;
    }
}