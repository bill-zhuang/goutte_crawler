<?php
/*
 * No use, too title data.
 * */
require_once 'Crawl_Base.php';
class test51 extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;
    private $_wenku_prefix;

    public function __construct($is_crontab)
    {
        parent::__construct();
        $this->crawl_urls = [
            0 => 'http://www.51test.net/chuzhongaoshu/',
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'exam' => 'crawl_test51'
        ];
        $this->_url_prefix = 'http://www.51test.net';
        $this->_is_crontab = boolval($is_crontab);
        $this->_save_dir = 'D:/gclass_crawl_exam/51test/';
        $this->_wenku_prefix = 'http://wenku.baidu.com/view/';
    }

    public function run()
    {
        $aoshu_urls = $this->_getAoshuUrl($this->crawl_urls[0]);
        foreach ($aoshu_urls as $aoshu_url)
        {
            $exam_data = $this->_crawlExam($aoshu_url);
            $this->_insertData($exam_data);
        }

        $this->_updateExamCoin();
    }

    private function _getAoshuUrl($url)
    {
        $aoshu_css_selector = 'div#page-container div#page-daohang div#daohang-right.title14b a';
        $this->adapter_goutte->sendRequest($url);
        $urls = $this->adapter_goutte->getHrefAttr($aoshu_css_selector);
        if (!empty($urls))
        {
            unset($urls[0]);
        }

        return $urls;
    }

    private function _crawlExam($url)
    {
        $data = [];
        $exam_css_selector = 'div#page-container div#page-news-list div.news-list-left div.news-list-left-content ul li a';
        $this->adapter_goutte->sendRequest($url);
        $exam_names = $this->adapter_goutte->getText($exam_css_selector);
        $exam_urls = $this->adapter_goutte->getHrefAttr($exam_css_selector);
        $this->adapter_goutte->setFakeHeaderIP();

        $baidu_wenku_css_selector = 'div.show_content p object#reader param';
        foreach ($exam_names as $key => $exam_name)
        {
            if (!$this->_isUrlExist($this->_url_prefix . $exam_urls[$key]))
            {
                //'http://www.51test.net/show/4563138.html'
                $this->adapter_goutte->sendRequest($this->_url_prefix . $exam_urls[$key]);
                $wenku_data = $this->adapter_goutte->getAttrByName($baidu_wenku_css_selector, 'value');
                if (!empty($wenku_data))
                {
                    $query_arr = parse_url($wenku_data[3]);
                    parse_str($query_arr['query'], $query);
                    $data[$query['title']] = [
                        'docid' => $query['docid'],
                        'doctype' => $query['doctype'],
                        'url' => $this->_url_prefix . $exam_urls[$key]
                    ];
                }
            }
        }

        return $data;
    }

    private function _updateExamCoin()
    {
        $columns = ['ctid', 'wenku_url'];
        $where = [
            'AND' => [
                'status[=]' => 0
            ]
        ];
        $data = $this->adapter_db->selectAll($this->table_names['exam'], $columns, $where);
        $wenke_coin_css_selector = 'div.btn-download span';
        foreach ($data as $value)
        {
            $wenku_url = $value['wenku_url'];
            $this->adapter_goutte->sendRequest($wenku_url);
            $price_data = $this->adapter_goutte->getText($wenke_coin_css_selector);
            $price = 0;
            if (!empty($price_data))
            {
                $price = preg_replace('/[^\d]+/', '', $price_data[0]);
            }

            $update_data = [
                'coin' => $price,
                'status' => 1,
                'update_time' => date('Y-m-d H:i:s'),
            ];
            $where = [
                'ctid[=]' => $value['ctid']
            ];
            $this->adapter_db->update($this->table_names['exam'], $update_data, $where);
        }
    }

    private function _insertData(array $data)
    {
        foreach ($data as $title => $value)
        {
            $cgid = $this->_getCgid($title);
            $insert_data = [
                'cgid' => $cgid,
                'title' => $title,
                'wenku_url' => $this->_wenku_prefix . $value['docid'],
                'file_type' => $value['doctype'],
                'coin' => 0,
                'url' => $value['url'],
                'status' => 0,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ];
            $this->adapter_db->insert($this->table_names['exam'], $insert_data);
        }
    }

    private function _isUrlExist($url)
    {
        $column = 'ctid';
        $where = [
            'AND' => [
                'url[=]' => $url
            ]
        ];
        $ctid = $this->adapter_db->selectOne($this->table_names['exam'], $column, $where);

        return ($ctid > 0) ? true : false;
    }

    private function _getCgid($title)
    {
        $grade_name = $this->_getExamGrade($title);
        $column = 'cg_id';
        $where = [
            'AND' => [
                'cg_name[=]' => $grade_name,
                'cg_status[=]' => 1
            ]
        ];
        $cgid = $this->adapter_db->selectOne($this->table_names['grade'], $column, $where);
        $cgid = intval($cgid);

        return $cgid;
    }

    private function _getExamGrade($title)
    {
        if (mb_strpos($title, '初三') !== false)
        {
            return '初三';
        }
        if (mb_strpos($title, '初二') !== false)
        {
            return '初三';
        }
        if (mb_strpos($title, '初一') !== false)
        {
            return '初三';
        }
        if (mb_strpos($title, '九年级') !== false)
        {
            return '初三';
        }
        if (mb_strpos($title, '八年级') !== false)
        {
            return '初三';
        }
        if (mb_strpos($title, '七年级') !== false)
        {
            return '初三';
        }

        return '初三';
    }
}

$test = new test51(false);
$test->run();