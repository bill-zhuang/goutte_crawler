<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-13
 * Time: 上午11:31
 */

require_once 'Crawl_Base.php';
class G12e extends Crawl_Base
{
    private $_provinces;
    private $_is_crontab;
    private $_grade_prefix_css_selectors;

    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://www.g12e.com/shijuan/'
        ];
        $this->table_names = [
            'province' => 'province',
            'grade' => 'grade',
            'course' => 'course',
            'exam' => 'g12e'
        ];
        $this->url_prefix = 'http://www.g12e.com';
        $this->_is_crontab = false;
        $this->_grade_prefix_css_selectors = [
            '中考' => 'div#Tab1.index-zkst',
            '高考' => 'div#Tab2.index-zkst',
            '初一' => 'div#Tab2.jiangyi',
            '初二' => 'div#Tab3.jiangyi',
            '初三' => 'div#Tab4.jiangyi',
            '高一' => 'div#Tab5.jiangyi',
            '高二' => 'div#Tab6.jiangyi',
            '高三' => 'div#Tab7.jiangyi'
        ];
        $this->_setAllProvince();
    }

    public function run()
    {
        $this->_is_crontab = false;
        $this->_crawlGradeAndCourse();
    }

    public function runCrontab()
    {
        $this->_is_crontab = true;
        $this->_crawlGradeAndCourse();
    }

    private function _crawlGradeAndCourse()
    {
        /*$grade_css_selector = 'div.tabtit ul span.toptit';
        $grades = $this->adapter_goutte->getText($grade_css_selector);*/
        $course_postfix_css_selector = 'div.tabtit ul li a';
        foreach ($this->_grade_prefix_css_selectors as $grade_name => $prefix)
        {
            $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
            $course_css_selector = $prefix . ' ' . $course_postfix_css_selector;
            $course_names = $this->adapter_goutte->getText($course_css_selector);
            $course_urls = $this->adapter_goutte->getHrefAttr($course_css_selector);
            $grid = $this->_getGrid($grade_name);
            foreach ($course_names as $course_key => $course_name)
            {
                $coid = $this->_getCoid($course_name);
                $course_url = $this->url_prefix . $course_urls[$course_key];
                $this->_crawlCourseExam($course_url, $grid, $coid);
            }
        }
    }

    private function _getGrid($grade_name)
    {
        $column = 'gr_id';
        $where = [
            'AND' => [
                'gr_name[=]' => $grade_name,
                'gr_status[=]' => 1
            ]
        ];
        $grid = $this->adapter_db->selectOne($this->table_names['grade'], $column, $where);
        if (!$grid)
        {
            $insert_grade_data = [
                'gr_name' => $grade_name,
                'gr_status' => 1,
                'gr_create_time' => date('Y-m-d H:i:s'),
                'gr_update_time' => date('Y-m-d H:i:s')
            ];
            $grid = $this->adapter_db->insert($this->table_names['grade'], $insert_grade_data);
        }

        return $grid;
    }

    private function _getCoid($course_name)
    {
        $column = 'co_id';
        $where = [
            'AND' => [
                'co_name[=]' => $course_name,
                'co_status[=]' => 1
            ],
        ];
        $coid = $this->adapter_db->selectOne($this->table_names['course'], $column, $where);
        if (!$coid)
        {
            $insert_course_data = [
                'co_name' => $course_name,
                'co_status' => 1,
                'co_create_time' => date('Y-m-d H:i:s'),
                'co_update_time' => date('Y-m-d H:i:s')
            ];
            $coid = $this->adapter_db->insert($this->table_names['course'], $insert_course_data);
        }

        return $coid;
    }

    private function _crawlCourseExam($course_url, $grid, $coid)
    {
        $crontab_flag = false;
        $this->adapter_goutte->sendRequest($course_url);
        $exam_title_css_selector = 'div.listcont ul divnewslist li a';
        $exam_title_backup_css_selector = 'div.listcont ul li a';
        $total_page = $this->_getTotalPage();
        for ($i = 1; $i <= $total_page; $i++)
        {
            if ($i != 1)
            {
                $this->adapter_goutte->setFakeHeaderIP();
                $this->adapter_goutte->sendRequest($course_url . 'page' . $i . '.shtm');
            }
            $titles = $this->adapter_goutte->getText($exam_title_css_selector);
            $urls = $this->adapter_goutte->getHrefAttr($exam_title_css_selector);
            if (empty($titles))
            {
                //for css changed
                $titles = $this->adapter_goutte->getText($exam_title_backup_css_selector);
                $urls = $this->adapter_goutte->getHrefAttr($exam_title_backup_css_selector);
            }

            foreach ($titles as $title_key => $title)
            {
                if ($this->_is_crontab)
                {
                    if ($this->adapter_db->selectCount($this->table_names['exam'], ['g1_title[=]' => $title]) == 0)
                    {
                        $crontab_flag = true;
                        break;
                    }
                }

                $exam_url = $this->url_prefix . $urls[$title_key];
                $prid = $this->_getPrid($title);
                $exam_content = $this->_getExamContent($exam_url);
                $download_url = (substr($exam_content, -3) == 'doc') ? $exam_content : '';

                $exam_content = ($download_url == '') ? $exam_content : '';
                $insert_exam_data = [
                    'gr_id' => $grid,
                    'co_id' => $coid,
                    'g1_title' => $title,
                    'pr_id' => $prid,
                    'g1_download_url' => $download_url,
                    'g1_content' => $exam_content,
                    'g1_url' => $exam_url,
                    'g1_status' => 1,
                    'g1_create_time' => date('Y-m-d H:i:s'),
                    'g1_update_time' => date('Y-m-d H:i:s')
                ];
                $this->adapter_db->insert($this->table_names['exam'], $insert_exam_data);
            }

            if ($this->_is_crontab && $crontab_flag)
            {
                break;
            }
        }
    }

    private function _getTotalPage()
    {
        $total_page = 1;

        $total_page_css_selector = 'div.showpage div.plist div.p1 a';
        $page_content = $this->adapter_goutte->getHrefAttr($total_page_css_selector);
        if (!empty($page_content))
        {
            $total_page = intval(str_replace(['page', '.shtm'], '', $page_content[count($page_content) - 1]));
        }

        return $total_page;
    }

    private function _getExamContent($exam_url)
    {
        $this->adapter_goutte->sendRequest($exam_url);
        $exam_content_css_selector = 'div#fontzoom.content.clearfix p';
        $exam_content_doc_css_selector = 'div#fontzoom.content.clearfix p a';
        $exam_content = $this->adapter_goutte->getHtml($exam_content_css_selector);
        $exam_doc_urls = $this->adapter_goutte->getHrefAttr($exam_content_doc_css_selector);
        //check if content or ms doc download link
        foreach ($exam_doc_urls as $exam_doc_url)
        {
            if (substr($exam_doc_url, -3) == 'doc')
            {
                if (substr($exam_doc_url, 0, 4) != 'http')
                {
                    $exam_doc_url = $this->url_prefix . $exam_doc_url;
                }
                return $exam_doc_url;
            }
        }

        return implode("\r\n", $exam_content);
    }

    private function _getPrid($title)
    {
        foreach ($this->_provinces as $prid => $province)
        {
            if (mb_strpos($title, $province) !== false)
            {
                return $prid;
            }
        }

        return 0;
    }

    private function _setAllProvince()
    {
        $column = ['pr_id', 'pr_name'];
        $where = [
            'pr_status[=]' => 1
        ];
        $province_data = $this->adapter_db->selectAll($this->table_names['province'], $column, $where);
        foreach ($province_data as $province_value)
        {
            $this->_provinces[$province_value['pr_id']] = str_replace(['省', '市'], '', $province_value['pr_name']);
        }
    }

    private function _downloadDoc($save_dir)
    {
        require_once '../models/Download.php';
        $download = new Download();

        $fetch_fields = [
            'g1_id',
            'g1_download_url',
        ];
        $where = [
            'AND' => [
                'g1_status[=]' => 1,
                'g1_content[=]' => '',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            $download_arr = [];
            foreach ($download_data as $download_value)
            {
                $doc_name = $download_value['g1_id'] . '.' . pathinfo($download_value['g1_download_url'], PATHINFO_EXTENSION);
                $download_arr[$doc_name] = $download_value['g1_download_url'];
            }

            $download->curlMultipleDownloadToDisk($download_arr, $save_dir);
        }
    }

}

$test = new G12e();
$test->run();