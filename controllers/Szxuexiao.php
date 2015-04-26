<?php

require_once 'Crawl_Base.php';
class Szxuexiao extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;

    public function __construct($is_crontab)
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://www.szxuexiao.com/Examination/',
            'http://www.szxuexiao.com/Examination/index_zx.html',
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'course' => 'crawl_course',
            'exam' => 'crawl_szxuexiao',
        ];
        $this->_url_prefix = 'http://www.szxuexiao.com';
        $this->_is_crontab = boolval($is_crontab);
    }

    public function run()
    {
        foreach ($this->crawl_urls as $crawl_url)
        {
            $courses = $this->_getCourse($crawl_url);
            foreach ($courses as $course_name => $course_url)
            {
                $ccid = $this->_getCcid($course_name);
                //$this->adapter_goutte->setFakeHeaderIP();
                $this->_crawlExam($this->_url_prefix . $course_url, $ccid);
            }
        }
    }

    private function _getCourse($url)
    {
        $course_css_selector = 'div.LgBox div#LgBoxVouchnav a';
        $this->adapter_goutte->sendRequest($url);
        $courses = $this->adapter_goutte->getText($course_css_selector);print_r($courses);
        $urls = $this->adapter_goutte->getHrefAttr($course_css_selector);print_r($urls);exit;
        $data = [];
        foreach ($courses as $course_key => $course_name)
        {
            if ($course_key == 3)
            {
                continue;
            }
            $course_name = trim(mb_substr($course_name, 0, 2, 'UTF-8'));
            $data[$course_name] = $this->_url_prefix . $urls[$course_key];
        }
        /*$data = [
            '语文' => '/Examination/yuwen.html',
            '数学' => '/Examination/shuxue.html',
            '英语' => '/Examination/yingyu.html',
        ];*/

        return $data;
    }

    private function _crawlExam($exam_url, $ccid)
    {
        $exam_css_selector = 'div.leftBox div.examlist ul li.title a';
        $exam_content_css_selector = '';
        $this->adapter_goutte->sendRequest($exam_url);
        $titles = $this->adapter_goutte->getText($exam_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($exam_css_selector);
        foreach ($titles as $title_key => $title)
        {
            $paper_url = $this->_url_prefix . $urls[$title_key];
            if (!$this->_isExamExist($paper_url))
            {
                $this->adapter_goutte->sendRequest($paper_url);
                $content = $this->adapter_goutte->getText($exam_content_css_selector);
                $cgid = '';
                //todo get grade name from title & get grid
                $this->_insertExamData($cgid, $ccid, $title, $content, $paper_url);
            }
        }
    }

    private function _insertExamData($cgid, $ccid, $title, $content, $url)
    {
        $insert_data = [
            'cg_id' => $cgid,
            'cc_id' => $ccid,
            'cs_title' => $title,
            'cs_content' => $content,
            'cs_url' => $url,
            'cs_status' => 1,
            'cs_ctime' => date('Y-m-d H:i:s'),
            'cs_utime' => date('Y-m-d H:i:s'),
        ];
        $this->adapter_db->insert($this->table_names['exam'], $insert_data);
    }

    private function _isExamExist($url)
    {
        $column = 'cs_id';
        $where = [
            'AND' => [
                'cs_url[=]' => $url,
                'cs_status[=]' => 1
            ],
        ];
        $ckid = $this->adapter_db->selectOne($this->table_names['exam'], $column, $where);

        return $ckid ? true : false;
    }

    private function _getCcid($course_name)
    {
        $column = 'cc_id';
        $where = [
            'AND' => [
                'cc_name[=]' => $course_name,
                'cc_status[=]' => 1
            ],
        ];
        $ccid = $this->adapter_db->selectOne($this->table_names['course'], $column, $where);
        if (!$ccid)
        {
            $ccid = $this->_insertCourse($course_name);
        }

        return $ccid;
    }

    private function _insertCourse($course_name)
    {
        $insert_course_data = [
            'cc_name' => $course_name,
            'cc_status' => 1,
            'cc_ctime' => date('Y-m-d H:i:s'),
            'cc_utime' => date('Y-m-d H:i:s')
        ];
        $ccid = $this->adapter_db->insert($this->table_names['course'], $insert_course_data);

        return $ccid;
    }

    private function _getGrid($grade_name)
    {
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
}

$test = new Szxuexiao(false);
$test->run();