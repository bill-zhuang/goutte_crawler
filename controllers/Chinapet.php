<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午3:29
 */

require_once '../models/Goutte_Crawl.php';
require_once '../models/DBTableFactory.php';
class Chinapet
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
    private $_post_limit;
    private $_table_names;

    public function __construct()
    {
        set_time_limit(0);
        $this->_adapter_goutte = new Goutte_Crawl();
        $this->_adapter_db = new DBTableFactory();
        $this->_crawl_urls = [
            'dog' => [
                '汪星人' => 'http://bbs.chinapet.com/forum-496-1.html',
                '金毛' => 'http://bbs.chinapet.com/forum-47-1.html',
                '贵宾/泰迪' => 'http://bbs.chinapet.com/forum-185-1.html',
                '比熊' => 'http://bbs.chinapet.com/forum-142-1.html',
                '哈士奇' => 'http://bbs.chinapet.com/forum-133-1.html',
                '博美' => 'http://bbs.chinapet.com/forum-184-1.html'
            ],
            'cat' => [
                '喵星人' => 'http://bbs.chinapet.com/forum-568-1.html',
                '土猫' => 'http://bbs.chinapet.com/forum-336-1.html',
                '美短' => 'http://bbs.chinapet.com/forum-341-1.html',
                '英短' => 'http://bbs.chinapet.com/forum-343-1.html',
                '波斯' => 'http://bbs.chinapet.com/forum-337-1.html',
                '折耳' => 'http://bbs.chinapet.com/forum-342-1.html'
            ],
            'small_pet' => [
                '龙猫' => 'http://bbs.chinapet.com/forum-583-1.html',
                '仓鼠' => 'http://bbs.chinapet.com/forum-584-1.html',
                '兔子' => 'http://bbs.chinapet.com/forum-346-1.html',
                '鸟类' => 'http://bbs.chinapet.com/forum-585-1.html',
                '两栖' => 'http://bbs.chinapet.com/forum-586-1.html',
                '爬宠' => 'http://bbs.chinapet.com/forum-322-1.html'
            ],
            'water_animal' => [
                '海洋' => 'http://bbs.chinapet.com/forum-581-1.html',
                '淡水' => 'http://bbs.chinapet.com/forum-582-1.html',
                '金鱼' => 'http://bbs.chinapet.com/forum-323-1.html',
                '选景' => 'http://bbs.chinapet.com/forum-580-1.html'
            ]
        ];
        $this->_post_limit = 100;
        $this->_table_names = [
            'main_category' => 'chinapet_main_category',
            'sub_category' => 'chinapet_sub_category',
            'post' => 'chinapet_post',
            'post_info' => 'chinapet_post_info'
        ];
    }

    public function run()
    {
        $this->_crawlPost();
    }

    private function _crawlPost()
    {
        foreach ($this->_crawl_urls as $category => $content)
        {
            $main_category_data = [
                'cmc_name' => $category
            ];
            $this->_adapter_db->insert($this->_table_names['main_category'], $main_category_data);
            foreach ($content as $animal => $url)
            {
                $sub_category_data = [
                    'csc_main_category' => $category,
                    'csc_name' => $animal,
                    'csc_url' => $url
                ];
                $this->_adapter_db->insert($this->_table_names['sub_category'], $sub_category_data);
                $total_page = 1;
                $total_data = $this->_getPageNum($url);
                if (!empty($total_data))
                {
                    $total_page = intval($total_data[0]);
                }
                $all_post_data = $this->_getPost($url, $total_page, $category, $animal);
                foreach ($all_post_data['name'] as $post_key => $post_name)
                {
                    $post_data = [
                        'cp_main_category' => $category,
                        'cp_sub_category' => $animal,
                        'cp_status' => 1,
                        'cp_name' => $post_name,
                        'cp_url' => $all_post_data['url'][$post_key]
                    ];
                    $this->_adapter_db->insert($this->_table_names['post'], $post_data);
                    $post_info = $this->_getPostInfo($all_post_data['url'][$post_key]);
                    //post detail
                    if (!empty($post_info))
                    {
                        $post_info_data = [
                            'cpi_main_category' => $category,
                            'cpi_sub_category' => $animal,
                            'cpi_status' => 1,
                            'cpi_name' => $post_name,
                            'cpi_url' => $all_post_data['url'][$post_key],
                            'cpi_content' => $post_info['content'],
                            'cpi_image' => $post_info['image']
                        ];
                        $this->_adapter_db->insert($this->_table_names['post_info'], $post_info_data);
                    }
                }
            }
        }
    }

    private function _getPageNum($url)
    {
        $page_css_selector = 'div.bm.bw0.pgs.cl span#fd_page_bottom div.pg a.last';
        $this->_adapter_goutte->sendRequest($url);
        $page_content = $this->_adapter_goutte->getText($page_css_selector);
        $page_content = array_map(
            function($str){
                return str_replace('.', '', $str);
            },
            $page_content
        );

        /*print_r($page_content);
        exit;*/

        return $page_content;
    }

    private function _getPost($url, $total_page, $category_value, $content_value)
    {
        $post_name_css_selector = 'table#threadlisttableid tbody[id^=normalthread] tr th a.s.xst';
        $data = [
            'name' => [],
            'url' => []
        ];
        for ($i = 1; $i <= $total_page; $i++)
        {
            if (count($data['name']) > 100)
            {
                break;
            }

            if ($i != 1)
            {
                $visit_url = str_replace('-1.html', '-' . $i . '.html', $url);
                $this->_adapter_goutte->sendRequest($visit_url);
            }

            $post_data = [
                'name' => [],
                'url' => []
            ];
            $post_data['name'] = $this->_adapter_goutte->getText($post_name_css_selector);
            $post_data['url'] = $this->_adapter_goutte->getHrefAttr($post_name_css_selector);
            /*print_r($post_data);
            exit;*/

            $data['name'] = array_merge($data['name'], $post_data['name']);
            $data['url'] = array_merge($data['url'], $post_data['url']);
        }

        return $data;
    }

    private function _getPostInfo($post_url)
    {
        $detail_css_selector = 'div.pct div.pcb div.t_fsz table tr td[id^=postmessage]';
        $image_css_selector = 'td.plc div.pct div.pcb div.t_fsz div.pattl dl.tattl.attm dd div.mbn.savephotop img';
        $data = [
            'content' => [],
            'image' => []
        ];
        $total_data = $this->_getPostPage($post_url);
        $total_page = 1;
        if (!empty($total_data))
        {
            $total_page = intval($total_data[0]);
        }
        for ($i = 1; $i <= $total_page; $i++)
        {
            if ($i != 1)
            {
                $actual_url = str_replace('1-1.html', $i . '-1.html', $post_url);
                $this->_adapter_goutte->sendRequest($actual_url);
            }

            $post_data = $this->_adapter_goutte->getText($detail_css_selector);
            $image_data = $this->_adapter_goutte->getAttrByName($image_css_selector, 'file');
            $data['content'] = array_merge($data['content'], $post_data);
            $data['image'] = array_merge($data['image'], array_filter($image_data));
        }
        /*print_r($data);
        exit;*/

        return $data;
    }

    private function _getPostPage($post_url)
    {
        $page_css_selector = 'div.pg label span';
        $this->_adapter_goutte->sendRequest($post_url);
        $page_content = $this->_adapter_goutte->getText($page_css_selector);
        $page_content = array_map(
            function($str){
                $is_match = preg_match('/(\d+)/', $str, $matches);
                $total_page = 1;
                if ($is_match > 0)
                {
                    $total_page = $matches[1];
                }
                return $total_page;
            },
            $page_content
        );
        /*print_r($page_content);
        exit;*/

        return $page_content;
    }
}

$test = new Chinapet();
$test->run();