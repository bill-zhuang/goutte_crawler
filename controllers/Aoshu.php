<?php

require_once 'Crawl_Base.php';
class Aoshu extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;

    public function __construct($is_crontab)
    {
        parent::__construct();
        $this->crawl_urls = [
            //'http://tiku.aoshu.com/'
            1 => 'http://tiku.aoshu.com/xiaoxue/',
            2 => 'http://tiku.aoshu.com/xsc/',
            3 => 'http://tiku.aoshu.com/aoshu/',
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'course' => 'crawl_course',
            'city' => 'city',
            'exam' => 'crawl_aoshu'
        ];
        $this->_url_prefix = 'http://tiku.aoshu.com';
        $this->_is_crontab = boolval($is_crontab);
        $this->_save_dir = 'D:/crawl_exam/aoshu/';
    }

    public function run()
    {
        foreach ($this->crawl_urls as $type => $crawl_url)
        {
            $filter_conditions = $this->_getFilterConditions($crawl_url);
            $cartesian_products = $this->_getCartesianProduct($filter_conditions, $type);
            foreach ($cartesian_products as $item)
            {
                $page_url = $crawl_url . $item['filter_id'];
                $total_page = $this->_getTotalPage($page_url);
                for ($i = 1; $i <= $total_page; $i++)
                {
                    $this->adapter_goutte->setFakeHeaderIP();
                    if ($i != 1)
                    {
                        $exam_url = $page_url . '/pg' . ($i * 10);
                    }
                    else
                    {
                        $exam_url = $page_url;
                    }

                    $this->_crawlExam($exam_url, $type, $item['cgid'], $item['ccid'], $item['ctid']);
                    //prevent crawl website server down
                    sleep(2);
                }
            }
        }
    }

    private function _getFilterConditions($url)
    {
        $filter_name_css_selector = 'div.menufix p strong';
        $filter_css_selector = 'div.menufix p a';
        $this->adapter_goutte->sendRequest($url);
        $filters = $this->adapter_goutte->getText($filter_name_css_selector);
        $condition_names = $this->adapter_goutte->getText($filter_css_selector);
        $condition_urls = $this->adapter_goutte->getHrefAttr($filter_css_selector);
        //data's key associate with array table_names's key
        $data = [
            'grade' => [],
            'course' => [],
            'city' => [],
        ];
        //used_filter's value associate with array data's key
        $used_filters = [
            '年级' => 'grade',
            '科目' => 'course',
            '地区' => 'city',
        ];

        $map = [];
        foreach ($filters as $filter_name)
        {
            $filter_name = str_replace('：', '', $filter_name);
            if (isset($used_filters[$filter_name]))
            {
                $map[] = $used_filters[$filter_name];
            }
            else
            {
                $map[] = '';
            }
        }

        $all_count = -1;
        foreach ($condition_names as $condition_key => $condition_name)
        {
            if ($condition_name != '全部')
            {
                if ($map[$all_count] !== '')
                {
                    $filter_id = str_replace($url, '', $condition_urls[$condition_key]);
                    $filter_id = str_replace(['/', '、'], '', $filter_id);
                    $data[$map[$all_count]][$condition_name] = $filter_id;
                }
            }
            else
            {
                $all_count++;
            }
        }

        foreach ($data as $data_key => $data_value)
        {
            if (empty($data_value))
            {
                $data[$data_key] = ['' => ''];
            }
        }

        return $data;
    }

    private function _getCartesianProduct(array $filter_conditions, $type)
    {
        $cartesian_products = [];
        foreach ($filter_conditions['grade'] as $grade_name => $grade_id)
        {
            $cgid = ($grade_name == '') ? 0 : $this->_getCgid($grade_name);
            foreach ($filter_conditions['course'] as $course_name => $course_id)
            {
                if ($type == 3)
                {
                    $course_name = '奥数';
                }
                $ccid = ($course_name == '') ? 0 : $this->_getCcid($course_name);
                foreach ($filter_conditions['city'] as $city_name => $city_id)
                {
                    $ctid = ($city_name == '') ? 0 : $this->_getCtid($city_name);
                    $cartesian_products[] = [
                        'filter_id' => $grade_id . $course_id . $city_id,
                        'cgid' => $cgid,
                        'ccid' => $ccid,
                        'ctid' => $ctid,
                    ];
                }
            }
        }

        return $cartesian_products;
    }

    private function _getTotalPage($url)
    {
        $total_page = 1;
        $total_page_css_selector = 'div.result-area div.page-b a';
        $this->adapter_goutte->sendRequest($url);
        $total_pages = $this->adapter_goutte->getText($total_page_css_selector);
        if (!empty($total_pages))
        {
            $total_page = intval($total_pages[count($total_pages) -2]);
            if ($total_page < 1)
            {
                $total_page = 1;
            }
        }

        return $total_page;
    }

    private function _crawlExam($exam_url, $type, $cgid = 0, $ccid = 0, $ctid = 0)
    {
        $exam_css_selector = 'div.result-area article.result-item h2 a';
        $exam_type_css_selector = 'div.result-area article.result-item h2 s';
        $this->adapter_goutte->sendRequest($exam_url);
        $titles = $this->adapter_goutte->getText($exam_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($exam_css_selector);
        $types = $this->adapter_goutte->getAttrByName($exam_type_css_selector, 'class');

        /*//check last url, for crawl website down(crawl unfinished)
        if (!empty($urls) && $this->_isExamExist($urls[count($urls) - 1]))
        {
            return;
        }*/

        foreach ($urls as $url_key => $url)
        {
            $caid = $this->_isExamExist($url);
            if ($caid == false)
            {
                $download_url = $this->_getExamDownloadUrl($url, $type);
                $file_type = str_replace('format for-', '', $types[$url_key]);
                $this->_insertExamData($cgid, $ccid, $ctid, $titles[$url_key], $download_url, $url, $file_type);
            }
            /*else
            {
                $file_type = str_replace('format for-', '', $types[$url_key]);
                $update_data = [
                    'ca_file_type' => $file_type,
                    'ca_update_time' => date('Y-m-d H:i:s'),
                ];
                $where = [
                    'ca_id[=]' => $caid
                ];
                $this->adapter_db->update($this->table_names['exam'], $update_data, $where);
            }*/
        }
    }

    /*
     * exam url:
     * http://tiku.aoshu.com/detail/63523/
     *
     * download url:
     * http://tiku.aoshu.com/download/type1/id63523/
     * http://tiku.aoshu.com/download/type2/id63523/
     * http://tiku.aoshu.com/download/type3/id63523/
     * download file extension unknown
    */
    private function _getExamDownloadUrl($download_page_url, $type)
    {
        $search = 'detail/';
        $replace = 'download/type' . $type . '/id';
        return str_replace($search, $replace, $download_page_url);
    }

    private function _isExamExist($url)
    {
        $column = 'ca_id';
        $where = [
            'AND' => [
                'ca_url[=]' => $url,
                'ca_status[=]' => 1
            ],
        ];
        $caid = $this->adapter_db->selectOne($this->table_names['exam'], $column, $where);
        return $caid;

        //return $caid ? true : false;
    }

    private function _insertExamData($cgid, $ccid, $ctid, $title, $download_url, $url, $file_type)
    {
        $insert_data = [
            'cg_id' => $cgid,
            'cc_id' => $ccid,
            'ct_id' => $ctid,
            'ca_title' => $title,
            'ca_download_url' => $download_url,
            'ca_content' => '',
            'ca_file_type' => $file_type,
            'ca_url' => $url,
            'ca_status' => 1,
            'ca_create_time' => date('Y-m-d H:i:s'),
            'ca_update_time' => date('Y-m-d H:i:s'),
        ];
        $this->adapter_db->insert($this->table_names['exam'], $insert_data);
    }

    private function _getCcid($course_name)
    {
        $column = 'ccid';
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

    private function _getCtid($city_name)
    {
        $column = 'ct_id';
        $where = [
            'AND' => [
                'ct_status[=]' => 1
            ],
            'LIKE' => [
                'ct_name%' => $city_name
            ],
        ];
        $ctid = $this->adapter_db->selectOne($this->table_names['city'], $column, $where);
        $ctid = intval($ctid);

        return $ctid;
    }

    private function _downloadExamDoc()
    {
        require_once '../models/Download.php';
        $download = new Download();

        $fetch_fields = [
            'ca_id',
            'ca_download_url',
            'ca_file_type',
        ];
        $where = [
            'AND' => [
                'ca_status[=]' => 1,
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            $download_arr = [];
            foreach ($download_data as $download_value)
            {
                $doc_name = $download_value['ca_id'] . $this->_getExamExtension($download_value['ca_file_type']);
                if (!file_exists($this->_save_dir . $doc_name))
                {
                    $download_arr[$doc_name] = $download_value['ca_download_url'];
                }
            }

            if (!empty($download_arr))
            {
                //download one by one, pause one second. prevent crawl website server down
                $download->curlMultipleDownloadToDisk($download_arr, $this->_save_dir, 1, true, 1);
            }
        }
    }

    private function _getExamExtension($file_type)
    {
        $ext = '';

        switch($file_type)
        {
            case 'word':
                $ext = '.doc';
                break;
            case 'rar':
                $ext = '.rar';
                break;
            case 'def':
                //$ext = 'unknown';
                break;
            case 'ppt':
                $ext = '.ppt';
                break;
            case 'excel':
                $ext = '.xls';
                break;
            case 'pdf':
                $ext = '.pdf';
                break;
            default:
                break;
        }

        return $ext;
    }
}

$test = new Aoshu(false);
$test->run();