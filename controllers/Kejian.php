<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-30
 * Time: 下午4:29
 */

require_once 'Crawl_Base.php';
class Kejian extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;

    public function __construct($is_crontab)
    {
        parent::__construct();
        ini_set('xdebug.max_nesting_level', 0); //xdebug nesting level, used on install xdebug
        $this->crawl_urls = [
            'http://www.1kejian.com/shiti/'
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'course' => 'crawl_course',
            'exam' => 'crawl_kejian'
        ];
        $this->_url_prefix = 'http://www.1kejian.com';
        $this->_is_crontab = boolval($is_crontab);
    }

    public function run()
    {
        $courses = $this->_getCourse();
        foreach ($courses as $course_name => $course_url)
        {
            $ccid = $this->_getCcid($course_name);
            $grades = $this->_getGrade($course_url);
            foreach ($grades as $grade_name => $grade_url)
            {
                $cgid = $this->_getCgid($grade_name);
                list($course_id, $total_page) = $this->_getTotalPage($grade_url);
                if ($course_id != 0)
                {
                    for ($i = 1; $i <= $total_page; $i++)
                    {
                        $this->adapter_goutte->setFakeHeaderIP();
                        if ($i != 1)
                        {
                            $exam_url = $grade_url . 'shiti_' . $course_id . '_' . $i . '.html';
                        }
                        else
                        {
                            $exam_url = $grade_url;
                        }

                        $this->_crawlExam($exam_url, $cgid, $ccid);
                    }
                }
            }
        }
    }

    private function _getCourse()
    {
        $course_css_selector = 'div#nav_main2 div#nav_menu2 a';
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $courses = $this->adapter_goutte->getText($course_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($course_css_selector);
        $data = [];
        foreach ($courses as $course_key => $course_name)
        {
            if ($course_key == 0)
            {
                continue;
            }
            $course_name = trim(mb_substr($course_name, 0, 2, 'UTF-8'));
            $data[$course_name] = $this->_url_prefix . $urls[$course_key];
        }

        return $data;
    }

    private function _getGrade($course_url)
    {
        $grade_css_selector = 'div#mainright div.mainParentListArea div.mbox div.mtitle a.showtitle';
        $this->adapter_goutte->sendRequest($course_url);
        $grades = $this->adapter_goutte->getText($grade_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($grade_css_selector);
        $data = [];
        foreach ($grades as $grade_key => $grade_name)
        {
            $grade_name = trim(mb_substr($grade_name, 0, -4, 'UTF-8'));
            if (mb_strlen($grade_name, 'UTF-8') > 2)
            {
                //for primary school only
                $grade_name = mb_substr($grade_name, 2, 3, 'UTF-8');
            }
            $data[$grade_name] = $this->_url_prefix . $urls[$grade_key];
        }

        return $data;
    }

    private function _getTotalPage($url)
    {
        $total_page = 1;
        $course_id = 0;
        $total_page_css_selector = 'div#showpage table tr td a';
        $this->adapter_goutte->sendRequest($url);
        $total_pages = $this->adapter_goutte->getHrefAttr($total_page_css_selector);
        if (!empty($total_pages))
        {
            $preg_id_page = '/shiti_(\d+)_(\d+)/';
            $is_match = preg_match($preg_id_page, $total_pages[count($total_pages) - 1], $matches);
            if ($is_match)
            {
                $course_id = $matches[1];
                $total_page = $matches[2];
            }
        }

        return [$course_id, $total_page];
    }

    private function _crawlExam($exam_url, $cgid, $ccid)
    {
        $exam_css_selector = 'div#mainright div#list_m ul li a';
        $this->adapter_goutte->sendRequest($exam_url);
        $titles = $this->adapter_goutte->getText($exam_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($exam_css_selector);
        foreach ($titles as $title_key => $title)
        {
            if (!$this->_isExamExist($this->_url_prefix . $urls[$title_key]))
            {
                $download_url = $this->_getExamDownloadUrl($urls[$title_key]);
                /*if ($download_url !== '')
                {*/
                    $this->_insertExamData($cgid, $ccid, $title, $download_url, $this->_url_prefix . $urls[$title_key]);
                /*}*/
            }
        }
    }

    private function _getExamDownloadUrl($download_page_url)
    {
        $download_url = '';

        $this->adapter_goutte->sendRequest($download_page_url);
        $preg_exam_id = '/(\d+)\.html$/';
        $is_match = preg_match($preg_exam_id, $download_page_url, $matches);
        if ($is_match)
        {
            $exam_id = $matches[1];
            $download_page_url = 'http://www.1kejian.com/shiti/softdown.asp?softid=' . $exam_id;
            $this->adapter_goutte->clearCookie(); //clear cookie each time, prevent from banned by crawl website
            $this->adapter_goutte->sendRequest($download_page_url);
            $post_parameter_css_selector = 'div#mainright div#list_c div form a';
            $onclick_data = $this->adapter_goutte->getAttrByName($post_parameter_css_selector, 'onclick');
            if (isset($onclick_data[0]))
            {
                $is_match = preg_match_all('/(\d+)/', $onclick_data[0], $matches);
                if ($is_match)
                {
                    // !!!IMPORTANT set cookie, or no download link will return
                    $cookie_obj = $this->adapter_goutte->getAllCookies();
                    if (!empty($cookie_obj))
                    {
                        $this->adapter_goutte->setCookie($cookie_obj[0]);
                        $post_params = [
                            'rnd' => $matches[1][0],
                            'softid' => $matches[1][1],
                            'id' => $matches[1][2],
                            'downid' => 0,
                        ];
                        $post_url = 'http://www.1kejian.com/shiti/download.asp';
                        $this->adapter_goutte->sendRequest($post_url, 'POST', $post_params);
                        $download_url = (string)$this->adapter_goutte->getRedirectUrl();
                        //todo download exam content, download link will expire
                    }
                }
            }
        }

        return $download_url;
    }

    private function _insertExamData($cgid, $ccid, $title, $download_url, $url)
    {
        $insert_data = [
            'cg_id' => $cgid,
            'cc_id' => $ccid,
            'ck_title' => $title,
            'ck_download_url' => $download_url,
            'ck_content' => '',
            'ck_url' => $url,
            'ck_status' => 1,
            'ck_create_time' => date('Y-m-d H:i:s'),
            'ck_update_time' => date('Y-m-d H:i:s'),
        ];
        $this->adapter_db->insert($this->table_names['exam'], $insert_data);
    }

    private function _isExamExist($url)
    {
        $column = 'ck_id';
        $where = [
            'AND' => [
                'ck_url[=]' => $url,
                'ck_status[=]' => 1
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

}

$test = new Kejian(false);
$test->run();