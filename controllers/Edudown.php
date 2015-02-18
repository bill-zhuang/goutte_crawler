<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-2-4
 * Time: 上午10:51
 */

require_once 'Crawl_Base.php';
class Edudown extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;

    public function __construct($is_crontab)
    {
        parent::__construct();
        ini_set('xdebug.max_nesting_level', 0); //xdebug nesting level, used on install xdebug
        $this->crawl_urls = [
            'http://www.edudown.net/soft/'
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'course' => 'crawl_course',
            'exam' => 'crawl_edudown'
        ];
        $this->_url_prefix = 'http://www.7cxk.net';
        $this->_is_crontab = boolval($is_crontab);
        $this->_save_dir  = 'D:/crawl_exam/edudown/';
    }

    public function run()
    {
        $courses = $this->_getCourse();
        foreach ($courses as $course_name => $course_url)
        {
            $ccid = $this->_getCcid($course_name);
            //for first page, not related with other page.
            $this->_crawlExam($course_url, $ccid);
            //get total page
            $total_page = $this->_getTotalPage($course_url);
            for ($i = $total_page; $i >= 1; $i--)
            {
                $this->adapter_goutte->setFakeHeaderIP();
                $exam_url = str_replace('Index', 'List_' . $i, $course_url);
                $this->_crawlExam($exam_url, $ccid);
                sleep(2);
            }
        }

        $this->_updateDownloadUrl();
    }

    private function _getCourse()
    {
        $course_grade_name_css_selector = 'div#Leftbox1 div.kztdd > strong';
        $course_grade_url_css_selector = 'div#Leftbox1 div.kztdd > a';
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $course_grades = $this->adapter_goutte->getText($course_grade_name_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($course_grade_url_css_selector);
        $data = [];
        foreach ($course_grades as $course_key => $course_name)
        {
            if ($course_name != '')
            {
                $course_name = mb_substr($course_name, 0, 4, 'UTF-8');
                $course_name = $this->_getCourseName($course_name);
                if ($course_name !== '')
                {
                    $data[$course_name] = $urls[$course_key];
                }
            }
        }

        return $data;
    }

    private function _getCourseName($name)
    {
        $course_name = '';
        switch($name)
        {
            case '高中会考':
            case '竞赛试题':
                break;
            case '理科综合':
                $course_name = '理综';
                break;
            case '文科综合':
                $course_name = '文综';
                break;
            case '中考试题':
                $course_name = '中考';
                break;
            case '高考试题':
                $course_name = '高考';
                break;
            default:
                $course_name = mb_substr($name, 2, 2, 'UTF-8');
                break;
        }

        return $course_name;
    }

    private function _getTotalPage($url)
    {
        $total_page = 1;
        $total_page_css_selector = 'div#Leftbox1 div#ad301 div.showpage a';
        $this->adapter_goutte->sendRequest($url);
        $total_pages = $this->adapter_goutte->getHrefAttr($total_page_css_selector);
        if (!empty($total_pages))
        {
            $preg_id_page = '/List_(\d+)\.html$/';
            $is_match = preg_match($preg_id_page, $total_pages[0], $matches);
            if ($is_match)
            {
                $total_page = intval($matches[1]);
            }
        }

        return $total_page;
    }

    private function _crawlExam($exam_url, $ccid)
    {
        $exam_css_selector = 'div#content div.box_1 div#Leftbox1 div.lmlb ul li strong a';
        $this->adapter_goutte->sendRequest($exam_url);
        $titles = $this->adapter_goutte->getText($exam_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($exam_css_selector);
        //check last url, for crawl website down(crawl unfinished)
        /*if ($this->_isExamExist($urls[count($urls) - 1]))
        {
            return;
        }*/

        foreach ($urls as $url_key => $url)
        {
            if (!$this->_isExamExist($url))
            {
                $exam_id = $this->_getExamID($url);
                if ($exam_id !== 0)
                {
                    //not exact download url, redirect
                    $download_page_url = 'http://www.7cxk.net/Soft/Down.asp?SoftID=' . $exam_id;

                    //get grade name by regex from exam title, maybe return empty
                    $grade_name = $this->_getGradeName($titles[$url_key]);
                    $cgid = 0;
                    if ($grade_name !== '')
                    {
                        $cgid = $this->_getCgid($grade_name);
                    }

                    $this->_insertExamData($cgid, $ccid, $titles[$url_key], $download_page_url, $url);
                }
            }
        }
    }

    private function _getExamID($exam_url)
    {
        $preg_exam_id = '/(\d+)\.html$/';
        $is_match = preg_match($preg_exam_id, $exam_url, $matches);
        if ($is_match)
        {
            return $matches[1];
        }

        return 0;
    }

    private function _getGradeName($exam_title)
    {
        $grade_name = '';
        $preg_grade = '/(([一二三四五六七八九]年级)|(初[一二三])|(高[一二三]))/u';
        $is_match = preg_match($preg_grade, $exam_title, $matches);
        if ($is_match)
        {
            $grade_name = $matches[1];
            if ($grade_name == '高考')
            {
                $grade_name = '高三';
            }
            else if ($grade_name == '七年级')
            {
                $grade_name = '初一';
            }
            else if ($grade_name == '八年级')
            {
                $grade_name = '初二';
            }
            else if ($grade_name == '九年级')
            {
                $grade_name = '初三';
            }
        }

        return $grade_name;
    }

    private function _isExamExist($url)
    {
        $column = 'ce_id';
        $where = [
            'AND' => [
                'ce_url[=]' => $url,
                'ce_status[=]' => 1
            ],
        ];
        $ceid = $this->adapter_db->selectOne($this->table_names['exam'], $column, $where);

        return $ceid ? true : false;
    }

    private function _insertExamData($cgid, $ccid, $title, $download_url, $url)
    {
        $insert_data = [
            'ce_cgid' => $cgid,
            'cc_id' => $ccid,
            'ce_title' => $title,
            'ce_download_url' => $download_url,
            'ce_content' => '',
            'ce_url' => $url,
            'ce_status' => 1,
            'ce_create_time' => date('Y-m-d H:i:s'),
            'ce_update_time' => date('Y-m-d H:i:s'),
        ];
        $this->adapter_db->insert($this->table_names['exam'], $insert_data);
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
            'cc_create_time' => date('Y-m-d H:i:s'),
            'cc_update_time' => date('Y-m-d H:i:s')
        ];
        $ccid = $this->adapter_db->insert($this->table_names['course'], $insert_course_data);

        return $ccid;
    }

    private function _getCgid($grade_name)
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

    private function _updateDownloadUrl()
    {
        $fetch_fields = [
            'ce_id',
            'ce_download_url',
        ];
        $where = [
            'AND' => [
                'ce_status[=]' => 1,
                'ce_content[=]' => '',
            ],
            'LIKE' => [
                'ce_download_url%' => 'http://www.7cxk.net/Soft/Down.asp',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            //get redirect download url
            foreach ($download_data as $download_value)
            {
                try
                {
                    $this->adapter_goutte->sendRequest($download_value['download_url']);
                    $download_url = $this->adapter_goutte->getRedirectUrl();
                    $update_data = [
                        'ce_download_url' => $download_url,
                        'ce_update_time' => date('Y-m-d H:i:s')
                    ];
                    $where = [
                        'ce_id[=]' => $download_value['ce_id']
                    ];
                    $this->adapter_db->update($this->table_names['exam'], $update_data, $where);
                }
                catch (Exception $e)
                {
                    //mostly is timeout, do nothing here
                }
            }
        }
    }

    private function _downloadExamDoc()
    {
        require_once '../models/Download.php';
        $download = new Download();

        $fetch_fields = [
            'ce_id',
            'ce_download_url',
        ];
        $where = [
            'AND' => [
                'ce_status[=]' => 1,
            ],
            'LIKE' => [
                'ce_download_url%[!]' => 'http://www.7cxk.net/Soft/Down.asp',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            $download_arr = [];
            foreach ($download_data as $download_value)
            {
                $doc_name = $download_value['ce_id'] . '.' . pathinfo($download_value['ce_download_url'], PATHINFO_EXTENSION);
                if (!file_exists($this->_save_dir . $doc_name))
                {
                    $download_arr[$doc_name] = $download_value['ce_download_url'];
                }
            }

            if (!empty($download_arr))
            {
                $download->curlMultipleDownloadToDisk($download_arr, $this->_save_dir, 1, true, 1);
            }
        }
    }

}

$test = new Edudown(false);
$test->run();