<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 4/15/2018
 * Time: 4:55 PM
 */

namespace Tienmx\Crawler;

use Tienmx\Crawler\TraitCrawler\BaseTrait;
use Tienmx\Crawler\TraitCrawler\GetCrawlerType;

class Test
{
    protected $baseCralwer;

    public function __construct()
    {
//        $this->baseCralwer = $crawlerBase;
    }

    public function index()
    {
        $trait = new GetCrawlerType();
        var_dump($trait->getString('fdafas'));
    }
}