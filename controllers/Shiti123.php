<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-2-3
 * Time: 下午2:05
 */

require_once 'Crawl_Base.php';
class Shiti123 extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;
    private $_save_dir;

    public function __construct($is_crontab)
    {
        parent::__construct();
        ini_set('xdebug.max_nesting_level', 0);
        $this->crawl_urls = [
            '一年级' => 'http://www.4t123.com/one/',
            '二年级' => 'http://www.4t123.com/two/',
            '三年级' => 'http://www.4t123.com/three/',
            '四年级' => 'http://www.4t123.com/four/',
            '五年级' => 'http://www.4t123.com/five/',
            '六年级' => 'http://www.4t123.com/six/',
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'course' => 'crawl_course',
            'exam' => 'crawl_shiti123',
        ];
        $this->_url_prefix = 'http://www.4t123.com';
        $this->_is_crontab = boolval($is_crontab);
        $this->_save_dir = 'D:/crawl_exam/shiti123/';
    }

    public function run()
    {
        $ccid = $this->_getCcid('数学');
        foreach ($this->crawl_urls as $grade_name => $grade_url)
        {
            $cgid = $this->_getCgid($grade_name);
            list($course_id, $total_page) = $this->_getTotalPage($grade_url);
            if ($course_id != 0)
            {
                for ($i = 1; $i <= $total_page; $i++)
                {
                    //$this->adapter_goutte->setFakeHeaderIP();
                    if ($i != 1)
                    {
                        $exam_url = $grade_url . 'list_' . $course_id . '_' . $i . '.html';
                    }
                    else
                    {
                        $exam_url = $grade_url;
                    }

                    $this->_crawlExam($exam_url, $cgid, $ccid);
                }
            }
        }

        $this->updateDownloadUrl();
    }

    public function updateDownloadUrl()
    {
        $fetch_fields = [
            'cs_id',
            'cs_download_url',
        ];
        $where = [
            'AND' => [
                'cs_status[=]' => 1,
                'cs_content[=]' => '',
            ],
            'LIKE' => [
                'cs_download_url%' => 'http://www.4t123.com/plus/download.php'
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
                        'cs_download_url' => $download_url,
                        'cs_update_time' => date('Y-m-d H:i:s')
                    ];
                    $where = [
                        'cs_id[=]' => $download_value['cs_id']
                    ];
                    $this->adapter_db->update($this->table_names['exam'], $update_data, $where);
                }
                catch(Exception $e)
                {
                    //mostly is timeout, do nothing here
                }
            }
        }
    }

    public function downloadExamDoc()
    {
        require_once '../models/Download.php';
        $download = new Download();

        $fetch_fields = [
            'cs_id',
            'cs_download_url',
        ];
        $where = [
            'AND' => [
                'cs_status[=]' => 1,
                'cs_content[=]' => '',
            ],
            'LIKE' => [
                '%cs_download_url' => '.doc'
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            $download_arr = [];
            foreach ($download_data as $download_value)
            {
                $save_name = $download_value['cs_id'] . '.' . pathinfo($download_value['cs_download_url'], PATHINFO_EXTENSION);
                if (!file_exists($this->_save_dir . $save_name))
                {
                    $download_arr[$save_name] = $download_value['cs_download_url'];
                }
            }

            if (!empty($download_arr))
            {
                $download->curlMultipleDownloadToDisk($download_arr, $this->_save_dir);
            }
        }
    }

    private function _getTotalPage($url)
    {
        $total_page = 1;
        $course_id = 0;
        $total_page_css_selector = 'div.dede_pages ul.pagelist li a';
        $this->adapter_goutte->sendRequest($url);
        $total_pages = $this->adapter_goutte->getHrefAttr($total_page_css_selector);

        if (!empty($total_pages))
        {
            $preg_id_page = '/list_(\d+)_(\d+)/';
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
        $exam_css_selector = 'div.listbox ul.e2 li a.title';
        $this->adapter_goutte->sendRequest($exam_url);
        $titles = $this->adapter_goutte->getText($exam_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($exam_css_selector);
        foreach ($titles as $title_key => $title)
        {
            if (!$this->_isExamExist($this->_url_prefix . $urls[$title_key]))
            {
                $paper_url = $this->_url_prefix . $urls[$title_key];
                $content = $this->_getExamContentOrUrl($paper_url);
                $this->_insertExamData($cgid, $ccid, $title, $content, $paper_url);
            }
        }
    }

    private function _getExamContentOrUrl($exam_url)
    {
        $content_css_selector = 'div.viewbox div.content table tr td p';
        $download_redirect_css_selector = 'div.viewbox div.content ul.downurllist a';
        $download_url_css_selector = 'div.formbox table tr td li a';
        $this->adapter_goutte->sendRequest($exam_url);
        $content = $this->adapter_goutte->getText($content_css_selector);
        $url = $this->adapter_goutte->getHrefAttr($download_redirect_css_selector);
        if (isset($url[0]))
        {
            $this->adapter_goutte->sendRequest($url[0]);
            $download_url = $this->adapter_goutte->getHrefAttr($download_url_css_selector);
            if (isset($download_url[0]))
            {
                //not exact download url, redirect
                return $this->_url_prefix . $download_url[0];
            }
        }

        unset($content[0]);
        return $content;
    }

    private function _insertExamData($cgid, $ccid, $title, $content, $url)
    {
        $insert_data = [
            'cg_id' => $cgid,
            'cc_id' => $ccid,
            'cs_title' => $title,
            'cs_url' => $url,
            'cs_status' => 1,
            'cs_create_time' => date('Y-m-d H:i:s'),
            'cs_update_time' => date('Y-m-d H:i:s'),
        ];
        if (is_array($content))
        {
            //todo json decode exam content when fetch
            $insert_data['cs_content'] = json_encode($content);
            $insert_data['cs_download_url'] = '';
        }
        else
        {
            $insert_data['cs_content'] = '';
            $insert_data['cs_download_url'] = $content;
        }
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
        $csid = $this->adapter_db->selectOne($this->table_names['exam'], $column, $where);

        return $csid ? true : false;
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
        $ccid = intval($ccid);

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

$test = new Shiti123(false);
$test->run();