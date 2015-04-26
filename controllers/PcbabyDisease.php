<?php

require_once 'Crawl_Base.php';
class Pcbaby_Disease extends Crawl_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            1 => 'http://yuer.pcbaby.com.cn/yinger/jibing/', //age between 0 and 1
            2 => 'http://yuer.pcbaby.com.cn/youer/jibing/', //age between 1 and 3
            3 => 'http://yuer.pcbaby.com.cn/xuelingqian/jibing/' //age between 3 and 6
        ];
        $this->table_names = [
            'article' => 'pcbaby_disease_article',
            'content' => 'pcbaby_disease_article_content'
        ];
    }

    public function run()
    {
        $needle = 'http://yuer.pcbaby.com.cn';
        foreach ($this->crawl_urls as $crawl_key => $crawl_url)
        {
            $this->adapter_goutte->sendRequest($crawl_url);
            $total_page = $this->_getTotalPage();
            for ($i = 0; $i < $total_page; $i++)
            {
                $this->adapter_goutte->setFakeHeaderIP();
                if ($i != 0)
                {
                    $url = $crawl_url . '/index_' . $i . '.html';
                    $this->adapter_goutte->sendRequest($url);
                }

                $crawl_article_data = $this->_getArticleTitleAndUrl();
                foreach ($crawl_article_data['title'] as $key => $title)
                {
                    $article_url = $crawl_article_data['url'][$key];
                    //filter error url
                    if (strpos($article_url, $needle) !== false)
                    {
                        $article_data = [
                            'pda_title' => $title,
                            'pda_type' => $crawl_key,
                            'pda_url' => $article_url,
                            'pda_status' => 1,
                            'pda_create_time' => date('Y-m-d H:i:s'),
                            'pda_update_time' => date('Y-m-d H:i:s')
                        ];

                        $pda_id = $this->adapter_db->insert($this->table_names['article'], $article_data);
                        if ($pda_id > 0)
                        {
                            $article_content = $this->_getArticleDetail($article_url);
                            if ($article_content['content'] == '')
                            {
                                //only one page
                                $article_content = $this->_getArticleDetail($article_url, false);
                            }
                            $article_content_data = [
                                'pda_id' => $pda_id,
                                'pdac_title' => $title,
                                'pdac_content' => $article_content['content'],
                                'pdac_tag' => implode(',', $article_content['tags']),
                                'pdac_status' => 1,
                                'pdac_create_time' => date('Y-m-d H:i:s'),
                                'pdac_update_time' => date('Y-m-d H:i:s')
                            ];
                            $this->adapter_db->insert($this->table_names['content'], $article_content_data);
                        }
                    }
                }
            }
        }
    }

    private function _getTotalPage()
    {
        $total_page_css_selector = '#pages.pcbaby_page a';
        $total_page_data = $this->adapter_goutte->getText($total_page_css_selector);
        $total_page = 1;
        for ($i = count($total_page_data) - 1; $i >= 0; $i--)
        {
            if (is_numeric($total_page_data[$i]))
            {
                $total_page = intval($total_page_data[$i]);
                break;
            }
        }

        return $total_page;
    }

    private function _getArticleTitleAndUrl()
    {
        $article_css_selector = 'p.aList-title a';
        $titles = $this->adapter_goutte->getText($article_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($article_css_selector);
        return [
            'title' => $titles,
            'url' => $urls
        ];
    }

    private function _getArticleDetail($url, $all_page_flag = true)
    {
        if ($all_page_flag)
        {
            $full_article_content_url = str_replace('.html', '_all.html', $url);
        }
        else
        {
            $full_article_content_url = $url;
        }

        $this->adapter_goutte->sendRequest($full_article_content_url);
        $article_tags_css_selector = 'span.artLabel-title a';
        $tags = $this->adapter_goutte->getText($article_tags_css_selector);
        $content_css_selector = 'div.artText';
        $content = $this->adapter_goutte->getHtml($content_css_selector);
        $content = isset($content[0]) ? $content[0] : '';

        return [
            'tags' => $tags,
            'content' => $content
        ];
    }
}

$test = new Pcbaby_Disease();
$test->run();