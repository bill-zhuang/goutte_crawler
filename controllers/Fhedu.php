<?php

require_once 'Crawl_Base.php';
class Fhedu extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;

    public function __construct($is_crontab)
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://www.fhedu.cn/'
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'course' => 'crawl_course',
            'exam' => 'crawl_fhedu'
        ];
        $this->_url_prefix = 'http://res.fhedu.cn/';
        $this->_is_crontab = boolval($is_crontab);
        $this->_save_dir = 'D:/crawl_exam/fhedu/';
    }

    public function run()
    {
        //$this->_updateDownloadUrl();exit;
        //$this->_downloadExamDoc();exit;
        $data = $this->_getSchoolAndCourse();
        foreach ($data as $url => $course)
        {
            $ccid = $this->_getCcid($course);
            $exam_url = $this->_getExamStartPageUrl($url);
            if ($exam_url !== '' && substr($exam_url, 0, 4) === 'http')
            {
                $total_page = $this->_getTotalPage($exam_url);
                for ($i = 1; $i <= $total_page ; $i++)
                {
                    $this->adapter_goutte->setFakeHeaderIP();

                    $crawl_exam_url = $exam_url . '&PageNo=' . $i;
                    echo $crawl_exam_url, PHP_EOL;
                    $this->_crawlExam($crawl_exam_url, $ccid);
                }
            }
        }
    }

    private function _getSchoolAndCourse()
    {
        $data = [];

        $course_css_selector = 'div.bottom div#bottomRight div.subcontent a';
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $courses = $this->adapter_goutte->getText($course_css_selector);
        $course_urls = $this->adapter_goutte->getHrefAttr($course_css_selector);
        foreach ($courses as $key => $course)
        {
            $courses[$key] = mb_substr($course, 2, 2, 'UTF-8');
            $data[$course_urls[$key]] = mb_substr($course, 2, 2, 'UTF-8');
        }

        return $data;
    }

    private function _getExamStartPageUrl($url)
    {
        $exam_url_css_selector = 'div#MainMenu ul li ul li a';
        $this->adapter_goutte->sendRequest($url);
        $names = $this->adapter_goutte->getText($exam_url_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($exam_url_css_selector);
        foreach ($names as $key => $name)
        {
            if ($name == '试卷' || $name == '试题')
            {
                return $urls[$key];
            }
        }

        return '';
    }

    private function _getTotalPage($url)
    {
        $total_page = 1;

        $this->adapter_goutte->sendRequest($url);
        $full_html = $this->adapter_goutte->getWholeHtmlPage();
        $preg_total_page = '/getString\.pageCount\s*=\s*(\d+)/';
        $is_match = preg_match($preg_total_page, $full_html, $matches);
        if ($is_match)
        {
            $total_page = intval($matches[1]);
            $total_page = ($total_page > 0) ? $total_page : 1;
        }

        return $total_page;
    }

    private function _crawlExam($exam_url, $ccid)
    {
        $grade_css_selector = 'div#ResourcesTextCell div.Title span a';
        $exam_css_selector = 'div#ResourcesTextCell div.Title b a';
        $this->adapter_goutte->sendRequest($exam_url);
        $grade_names = [];
        $grades = $this->adapter_goutte->getText($grade_css_selector);
        $grade_urls = $this->adapter_goutte->getHrefAttr($grade_css_selector);
        foreach ($grade_urls as $key => $grade_url)
        {
            if (strpos($grade_url, '/index.asp?Search=@Res_GradeID=:') !== false)
            {
                $grade_names[] = $grades[$key];
            }
        }
        //todo check count equal to exam_names

        $exam_names = $this->adapter_goutte->getAttrByName($exam_css_selector, 'title');
        $exam_ids = $this->adapter_goutte->getHrefAttr($exam_css_selector);
        foreach ($exam_ids as $key => $exam_id)
        {
            $is_match = preg_match('/(\d+);$/', $exam_id, $matches);
            if ($is_match)
            {
                //download link: http://res.fhedu.cn/ResourcesDownload.asp?Res_ID=222456
                $download_url = 'http://res.fhedu.cn/ResourcesDownload.asp?Res_ID=' . $matches[1];
                $reflect_grade_name = $this->_reflectGradeName($grade_names[$key]);
                $cgid = $this->_getCgid($reflect_grade_name);
                $this->_insertExamData($cgid, $ccid, $exam_names[$key], $download_url,
                    $this->_url_prefix . str_replace(';', '', $exam_id));
            }
        }
    }

    private function _reflectGradeName($name)
    {
        switch($name)
        {
            case '一年级':
            case '二年级':
            case '三年级':
            case '四年级':
            case '五年级':
            case '六年级':
                break;
            case '初一':
            case '七年级':
                $name = '初一';
                break;
            case '初二':
            case '八年级':
                $name = '初二';
                break;
            case '初三':
            case '九年级':
            case '中考':
            case '中考专题':
            case '中考专区':
            case '中考相关':
            case '中考复习':
                $name = '初三';
                break;
            case '高一':
            case '高二':
                break;
            case '高三':
            case '高考专题':
            case '高考':
            case '高考复习':
            case '高考专区':
            case '高考相关':
                $name = '高三';
                break;
            default:
                break;
        }

        return $name;
    }

    private function _isExamExist($url)
    {
        $column = 'cf_id';
        $where = [
            'AND' => [
                'cf_url[=]' => $url,
                'cf_status[=]' => 1
            ],
        ];
        $cfid = $this->adapter_db->selectOne($this->table_names['exam'], $column, $where);
        return $cfid;

        //return $cfid ? true : false;
    }

    private function _insertExamData($cgid, $ccid, $title, $download_url, $url)
    {
        $insert_data = [
            'cg_id' => $cgid,
            'cc_id' => $ccid,
            'cf_title' => $title,
            'cf_download_url' => $download_url,
            'cf_content' => '',
            'cf_url' => $url,
            'cf_status' => 1,
            'cf_create_time' => date('Y-m-d H:i:s'),
            'cf_update_time' => date('Y-m-d H:i:s'),
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
            'cf_id',
            'cf_download_url',
        ];
        $where = [
            'AND' => [
                'cf_status[=]' => 1,
                'cf_content[=]' => ''
            ],
            'LIKE' => [
                'cf_download_url%' => 'http://res.fhedu.cn/ResourcesDownload.asp',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            //get redirect download url
            $preg_redirect_url = '/HREF="([^"]+)"/sm';
            foreach ($download_data as $download_value)
            {
                try
                {
                    $ch = curl_init($download_value['cf_download_url']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $curl_content = curl_exec($ch);
                    curl_close($ch);

                    if ($curl_content !== false)
                    {
                        $is_match = preg_match($preg_redirect_url, $curl_content, $matches);
                        if ($is_match)
                        {
                            $update_data = [
                                'cf_download_url' => 'http://res.fhedu.cn' . $matches[1],
                                'cf_update_time' => date('Y-m-d H:i:s')
                            ];
                            $where = [
                                'cf_id[=]' => $download_value['cf_id']
                            ];
                            $this->adapter_db->update($this->table_names['exam'], $update_data, $where);
                            echo $download_value['cf_id'], ' download_url updated', PHP_EOL;
                        }
                    }
                }
                catch (Exception $e)
                {
                    //mostly is timeout, do nothing here
                }
            }
        }
    }

    private function _updateDownloadUrlForSpeed()
    {
        $fetch_fields = [
            'cf_id',
            'cf_download_url',
        ];
        $where = [
            'AND' => [
                'cf_status[=]' => 1,
                'cf_content[=]' => ''
            ],
            'LIKE' => [
                'cf_download_url%' => 'http://res.fhedu.cn/ResourcesDownload.asp',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            //get redirect download url
            $preg_redirect_url = '/HREF="([^"]+)"/sm';
            $download_arr = [];
            foreach ($download_data as $download_value)
            {
                if (!file_exists($this->_save_dir . $download_value['cf_id']))
                {
                    $download_arr[$download_value['cf_id']] = $download_value['cf_download_url'];
                }
            }

            if (!empty($download_arr))
            {
                //download one by one, pause one second. prevent crawl website server down
                require_once '../models/Download.php';
                $download = new Download();
                $download->curlMultipleDownloadToDisk($download_arr, $this->_save_dir, 50, true, 1);

                $files = scandir($this->_save_dir);
                if ($files !== false)
                {
                    foreach ($files as $file)
                    {
                        if ($file != '.' && $file != '..')
                        {
                            $file_content = file_get_contents($this->_save_dir . $file);
                            if ($file_content !== false)
                            {
                                $is_match = preg_match($preg_redirect_url, $file_content, $matches);
                                if ($is_match)
                                {
                                    $update_data = [
                                        'cf_download_url' => 'http://res.fhedu.cn' . $matches[1],
                                        'cf_update_time' => date('Y-m-d H:i:s')
                                    ];
                                    $where = [
                                        'cf_id[=]' => $file
                                    ];
                                    $this->adapter_db->update($this->table_names['exam'], $update_data, $where);
                                    echo $file, ' download_url updated', PHP_EOL;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function _downloadExamDoc()
    {
        require_once '../models/Download.php';
        $download = new Download();

        $fetch_fields = [
            'cf_id',
            'cf_download_url',
        ];
        $where = [
            'AND' => [
                'cf_status[=]' => 1,
            ],
            'LIKE' => [
                'cf_download_url%' => 'http://res.fhedu.cn/htmledit/uploadfile/',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            $download_arr = [];
            foreach ($download_data as $download_value)
            {
                $doc_name = $download_value['cf_id'] . '.' . pathinfo($download_value['cf_download_url'], PATHINFO_EXTENSION);;
                if (!file_exists($this->_save_dir . $doc_name))
                {
                    $download_arr[$doc_name] = $download_value['cf_download_url'];
                }
            }

            if (!empty($download_arr))
            {
                //download one by one, pause one second. prevent crawl website server down
                $download->curlMultipleDownloadToDisk($download_arr, $this->_save_dir, 10, true, 1);
            }
        }
    }

}

$test = new Fhedu(false);
$test->run();