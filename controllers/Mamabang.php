<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午2:38
 */

/*
 * WARNING!!! untested & finished
 * */

require_once 'Crawl_Base.php';
class Mamabang extends Crawl_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://www.mmbang.com/bang/485#topics'
        ];
        $this->table_names = [
            'main_category' => 'mamabang_main_category',
            'sub_category' => 'mamabang_sub_category',
            'topic' => 'mamabang_topic',
            'content' => 'mamabang_topic_content'
        ];
    }

    public function run()
    {
        $this->_crawlTopics();
    }

    private function _crawlTopics()
    {
        $main_category_css_selector = 'div#page_body.container.body div#left_column.span-16.first div#sub_forums_div.span-15.container div.span-4.sub_forum a.blue';
        $topic_css_selector = 'div#page_body.container.body div#left_column.span-16.first div#tiopic_list.span-16.first.last.clear ul.clear li.topic_title a.blue';
        $topic_title_css_selector = 'div#page_body.container.body div#left_column.span-16.first div#topic_list.span-16.first.last.clear ul.clear li.topic_title a.blue';
        $topic_content_lz_css_selector = 'div#page_body.container.body div#left_column.span-16.first div.topic_info_div.span-15.clear div.span-13.last div#topic_content';
        $topic_content_reply_css_selector = 'div#page_body.container.body div#left_column.span-16.first div#post_{floor}.post_info_div.span-15.clear div.post_content_div.span-13.last div.post_content div#post_content_{floor}';

        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);

        $main_category_names = $this->adapter_goutte->getText($main_category_css_selector);
        $main_category_urls = $this->adapter_goutte->getText($main_category_css_selector);
        foreach ($main_category_names as $key => $main_category_name)
        {
            if ($key == 0)
            {
                continue;
            }

            if ($this->adapter_db->selectCount(
                    $this->table_names['main_category'],
                    [
                        'mmc_name[=]' => $main_category_name
                    ]
                ) == 0)
            {
                $main_category_data = [
                    'mmc_name' => $main_category_name
                ];
                $this->adapter_db->insert($this->table_names['main_category'], $main_category_data);
            }

            $main_category_url = 'http://www.mmbang.com' . $main_category_urls[$key];
            $this->adapter_goutte->sendRequest($main_category_url);
            $topic_names = $this->adapter_goutte->getText($topic_title_css_selector);
            $topic_urls = $this->adapter_goutte->getHrefAttr($topic_title_css_selector);
            foreach ($topic_names as $topic_key => $topic_name)
            {
                $topic_name = trim($topic_name);
                if (!is_numeric($topic_name))
                {
                    $topic_url = 'http://www.mmbang.com' . $topic_urls[$topic_key];
                    $topic_data = [
                        'mt_name' => $topic_name,
                        'mt_main_category' => $main_category_name,
                        'mt_url' => $topic_url
                    ];
                    $this->adapter_db->insert($this->table_names['topic'], $topic_data);
                    $this->adapter_goutte->sendRequest($topic_url);
                    $lz_content = $this->adapter_goutte->getText($topic_content_lz_css_selector);
                    //print_r($lz_content);exit;
                    $topic_content_data = [
                        'mtc_topic' => $topic_name,
                        'mtc_main_category' => $main_category_name,
                        'mtc_url' => $topic_url,
                        'mtc_content' => $lz_content[0],
                        'mtc_floor' => 0,
                        'mtc_status' => 1,
                    ];
                    $this->adapter_db->insert($this->table_names['content'], $topic_content_data);
                    for ($i = 1; $i <= 10; $i++)
                    {
                        $reply_content = $this->adapter_goutte->getText(str_replace('{floor}', $i, $topic_content_reply_css_selector));
                        //print_r($lz_content);exit;
                        if (isset($reply_content[0]))
                        {
                            $topic_content_data['content'] = $reply_content[0];
                            $topic_content_data['floor'] = $i;
                            $this->adapter_db->insert($this->table_names['content'], $topic_content_data);
                        }
                    }
                }
            }
        }
    }
}

$test = new Mamabang();
$test->run();