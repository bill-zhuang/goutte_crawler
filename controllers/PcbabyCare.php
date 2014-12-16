<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-11
 * Time: 下午2:59
 */
require_once '../models/Goutte_Crawl.php';
require_once '../models/DBTableFactory.php';
class Pcbaby_Care
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
            1 => 'http://yuer.pcbaby.com.cn/yinger/huli/', //age between 0 and 1
            2 => 'http://yuer.pcbaby.com.cn/youer/huli/', //age between 1 and 3
            3 => 'http://yuer.pcbaby.com.cn/xuelingqian/huli/' //age between 3 and 6
        ];
        $this->_table_names = [
            'article' => 'pcbaby_care_article',
            'content' => 'pcbaby_care_article_content'
        ];
    }

    public function run()
    {
        $needle = 'http://yuer.pcbaby.com.cn';
        foreach ($this->_crawl_urls as $crawl_key => $crawl_url)
        {
            $this->_adapter_goutte->sendRequest($crawl_url);
            $total_page = $this->_getTotalPage();
            for ($i = 0; $i < $total_page; $i++)
            {
                $this->_adapter_goutte->setFakeHeaderIP();
                if ($i != 0)
                {
                    $url = $crawl_url . '/index_' . $i . '.html';
                    $this->_adapter_goutte->sendRequest($url);
                }

                $crawl_article_data = $this->_getArticleTitleAndUrl();
                foreach ($crawl_article_data['title'] as $key => $title)
                {
                    $article_url = $crawl_article_data['url'][$key];
                    //filter error url
                    if (strpos($article_url, $needle) !== false)
                    {
                        $article_data = [
                            'title' => $title,
                            'type' => $crawl_key,
                            'url' => $article_url,
                            'status' => 1,
                            'ctime' => date('Y-m-d H:i:s'),
                            'utime' => date('Y-m-d H:i:s')
                        ];

                        $pcaid = $this->_adapter_db->insert($this->_table_names['article'], $article_data);
                        if ($pcaid > 0)
                        {
                            $article_content = $this->_getArticleDetail($article_url);
                            if ($article_content['content'] == '')
                            {
                                //only one page
                                $article_content = $this->_getArticleDetail($article_url, false);
                            }
                            $article_content_data = [
                                'pcaid' => $pcaid,
                                'title' => $title,
                                'content' => $article_content['content'],
                                'tag' => implode(',', $article_content['tags']),
                                'status' => 1,
                                'ctime' => date('Y-m-d H:i:s'),
                                'utime' => date('Y-m-d H:i:s')
                            ];
                            $this->_adapter_db->insert($this->_table_names['content'], $article_content_data);
                        }
                    }
                }
            }
        }
    }

    private function _getTotalPage()
    {
        $total_page_css_selector = '#pages.pcbaby_page a';
        $total_page_data = $this->_adapter_goutte->getText($total_page_css_selector);
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
        $titles = $this->_adapter_goutte->getText($article_css_selector);
        $urls = $this->_adapter_goutte->getHrefAttr($article_css_selector);
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

        $this->_adapter_goutte->sendRequest($full_article_content_url);
        $article_tags_css_selector = 'span.artLabel-title a';
        $tags = $this->_adapter_goutte->getText($article_tags_css_selector);
        $content_css_selector = 'div.artText';
        $content = $this->_adapter_goutte->getHtml($content_css_selector);
        $content = isset($content[0]) ? $content[0] : '';

        return [
            'tags' => $tags,
            'content' => $content
        ];
    }
}

$test = new Pcbaby_Care();
$test->run();