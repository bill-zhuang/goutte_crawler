<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午2:21
 */

require_once '../models/Goutte_Crawl.php';
require_once '../models/DBTableFactory.php';
class Yaolan
{
    /**
     * @var Goutte_Crawl
     */
    private $_adapter_goutte;
    /**
     * @var DBTableFactory
     */
    private $_adapter_db;
    private $_crawl_urls;
    private $_table_names;

    public function __construct()
    {
        $this->_adapter_goutte = new Goutte_Crawl();
        $this->_adapter_db = new DBTableFactory();
        $this->_crawl_urls = [
            'http://www.yaolan.com/edm/'
        ];
        $this->_table_names = [
            'main_category' => 'yaolan_main_category',
            'sub_category' => 'yaolan_sub_category',
            'article' => 'yaolan_article'
        ];
    }

    public function run()
    {
        $this->_crawlYaolanTips();
    }

    private function _crawlYaolanTips()
    {
        $title_css_selector = 'section.area.clear div.edm_l div.edm_box_l.clear div.schedule ul li a';
        $content_css_selector = 'section.area.clear div.edm_l div.edm_time div.time_box div.bar';
        $main_category_css_selector = 'div#page div.c_nav div.clear div.nav_two ul li a';
        $sub_category_css_selectors = [
            'div#page div#nav1.box.box1 div.bd.clear div#testBox1.box_r div.weekly_box.weekly_box1 div.weekly_boxs.weekly_boxs1 p.weekly_a.weekly_a1 a',
            'div#page div#nav2.box.box2 div.bd.clear div.box_r div.weekly_box div.weekly_boxs.weekly_boxs2 p.weekly_a.weekly_a2 a',
            'div#page div#nav3.box.box3 div.bd.clear div#testBox2.box_r div.weekly_box.weekly_box2 div.weekly_boxs.weekly_boxs1 ul.weekly_a.weekly_a3 li a',
            'div#page div#nav4.box.box4 div.bd.clear div#testBox3.box_r div.weekly_box.weekly_box4 div.weekly_boxs.weekly_boxs4 ul.weekly_a.weekly_a4 li a',
            'div#page div#nav5.box.box5 div.bd.clear div.box_r div.weekly_box div.weekly_boxs.weekly_boxs5 p.weekly_a.weekly_a5 a',
        ];

        $this->_adapter_goutte->sendRequest($this->_crawl_urls[0]);
        $cache_goutte_crawler = $this->_adapter_goutte; //for cache

        $main_category_names = $this->_adapter_goutte->getText($main_category_css_selector);
        foreach ($main_category_names as $key => $main_category_name)
        {
            if ($key == 0)
            {
                continue;
            }

            if ($this->_adapter_db->selectCount(
                    $this->_table_names['main_category'],
                    ['ymc_name[=]' => $main_category_name]
                ) == 0)
            {
                $main_category_data = [
                    'ymc_name' => $main_category_name
                ];
                $this->_adapter_db->insert($this->_table_names['main_category'], $main_category_data);
            }


            $this->_goutte_crawler = $cache_goutte_crawler;
            $all_sub_category = [
                'title' => [],
                'url' => []
            ];
            $all_sub_category['title'] = $this->_adapter_goutte->getAttrByName($sub_category_css_selectors[$key - 1], 'title');
            $all_sub_category['url'] = $this->_adapter_goutte->getHrefAttr($sub_category_css_selectors[$key - 1]);

            foreach ($all_sub_category['title'] as $title_key => $title)
            {
                $sub_category_data = [
                    'ysc_name' => $title,
                    'ysc_main_category' => $main_category_name,
                    'ysc_url' => $all_sub_category['url'][$title_key]
                ];
                $this->_adapter_db->insert($this->_table_names['sub_category'], $sub_category_data);

                $article_url = $all_sub_category['url'][$title_key];
                $this->_adapter_goutte->sendRequest($article_url);
                $titles = $this->_adapter_goutte->getText($title_css_selector);
                $contents = $this->_adapter_goutte->getHtml($content_css_selector);

                $article_data = [
                    'ya_sub_category' => $title,
                    'ya_main_category' => $main_category_name,
                    'ya_title' => '',
                    'ya_content' => '',
                    'ya_url' => $article_url,
                    'ya_status' => 1
                ];
                foreach ($contents as $content_key => $content)
                {
                    $article_data['ya_title'] = trim($titles[$content_key]);
                    $article_data['ya_content'] = $content;
                    $this->_adapter_db->insert($this->_table_names['article'], $article_data);
                }
            }
        }
    }
}

$test = new Yaolan();
$test->run();