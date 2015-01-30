<?php

require_once 'Crawl_Base.php';
class Zxxk_zuhe extends Crawl_Base
{
    private $_cookie_path;
    private $_url_prefix;

    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://zuhe.zxxk.com/',
        ];
        $this->table_names = [
            'province' => 'province',
            'exam' => 'zxxk_zuhe'
        ];
        $this->_cookie_path = '../cookie/zxxk_zuhe_cookie.txt';
        $this->_url_prefix = 'http://zuhe.zxxk.com';
    }

    public function run()
    {
        $free_exam_url = $this->_getFreeExamUrl();
        if ($free_exam_url !== '')
        {
            $this->_crawlFreeExam($free_exam_url);

            //download exam
            $save_dir = 'D:/zxxk_zuhe/';
            list ($login_url, $login_data) = $this->_getLoginInfoFromMobile();
            $this->_loginAndDownloadExam($login_url, $login_data, $save_dir);
        }
    }

    private function _getFreeExamUrl()
    {
        $free_css_selector = 'div.main_line div.topNav ul.clearfix li a';
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $combine_urls = $this->adapter_goutte->getHrefAttr($free_css_selector);

        $free_exam_key = 1;
        $free_exam_url = '';
        if (!empty($combine_urls))
        {
            $free_exam_url = $this->_url_prefix . $combine_urls[$free_exam_key];
        }

        return $free_exam_url;
    }

    private function _crawlFreeExam($free_exam_url)
    {
        $preg_province_id = '/(\/j\-\d+\-\d+)\-(\d+)/';

        $this->adapter_goutte->sendRequest($free_exam_url);
        $categories = $this->_getCategoryProvincesYears();
        $free_charge_url = $this->_getExamMoney();
        if ($free_charge_url != '')
        {
            foreach ($categories['category']['names'] as $category_name_key => $category_name)
            {
                if ($category_name == '试题试卷')
                {
                    foreach ($categories['province']['names'] as $province_name_key => $province_name)
                    {
                        $prid = $this->_getPrid($province_name);
                        $visit_url = $this->_url_prefix . $categories['category']['urls'][$category_name_key];
                        $is_match = preg_match($preg_province_id, $categories['province']['urls'][$province_name_key], $matches);
                        if ($is_match)
                        {
                            $visit_url = preg_replace($preg_province_id, '\1-' . $matches[2], $visit_url);
                            //crawl exam paper
                            $this->adapter_goutte->sendRequest($visit_url);
                            $total_pages = $this->_getTotalPage();
                            $this->_crawlExamPaper($visit_url, $total_pages, $prid);
                        }
                    }
                }
            }
        }
    }

    private function _getCategoryProvincesYears()
    {
        $category_css_selector = 'div.select_con.navbox div.soft_bk.soft_bk_bottom div.line div.left.rt a';
        $names = $this->adapter_goutte->getText($category_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($category_css_selector);
        $category_info = [
            'category' => [
                'names' => [],
                'urls' => [],
            ],
            'province' => [
                'names' => [],
                'urls' => [],
            ],
            'year' => [
                'names' => [],
                'urls' => [],
            ],
        ];
        $no_limit_count = 0;
        foreach ($names as $key => $name)
        {
            if ($name != '不限')
            {
                if ($no_limit_count == 1)
                {
                    $category_info['category']['names'][] = $name;
                    $category_info['category']['urls'][] = $urls[$key];
                }
                else if ($no_limit_count == 2)
                {
                    if ($name == '全国')
                    {
                        continue;//???
                    }
                    $category_info['province']['names'][] = $name;
                    $category_info['province']['urls'][] = $urls[$key];
                }
                else if ($no_limit_count == 3)
                {
                    $category_info['year']['names'][] = $name;
                    $category_info['year']['urls'][] = $urls[$key];
                }
                else
                {
                    break;
                }
            }
            else
            {
                $no_limit_count++;
            }
        }

        return $category_info;
    }

    private function _getExamMoney()
    {
        $money_css_selector = 'div.soft_bk.soft_bk_bottom div.line div.left.rt_sx a';
        $money_names = $this->adapter_goutte->getText($money_css_selector);
        $money_urls = $this->adapter_goutte->getHrefAttr($money_css_selector);
        foreach ($money_names as $key => $money_name)
        {
            if ($money_name == '免费')
            {
                return $money_urls[$key];
            }
        }

        return '';
    }

    private function _getTotalPage()
    {
        $total_page_css_selector = 'div.showpageList a.disabled';
        $total_page_text = $this->adapter_goutte->getText($total_page_css_selector);

        $preg = '/共(\d+)页/';
        if (!empty($total_page_text))
        {
            $is_match = preg_match($preg, $total_page_text[count($total_page_text) - 1], $matches);
            if ($is_match)
            {
                return intval($matches[1]);
            }
        }

        return 1;
    }

    private function _crawlExamPaper($url, $total_pages, $prid)
    {
        $exam_css_selector = 'div.list_top a.left';
        $exam_type_css_selector = 'div.list_top div.it_1';
        $download_url_format = 'http://download.zxxk.com/?UrlID=29&InfoID=%s';
        $insert_data = [
            'pr_id' => $prid,
            'zz_status' => 1,
            'zz_create_time' => date('Y-m-d H:i:s'),
            'zz_update_time' => date('Y-m-d H:i:s')
        ];
        for ($i = 1; $i <= $total_pages; $i++)
        {
            if ($i != 1)
            {
                $this->adapter_goutte->setFakeHeaderIP();
                $url = str_replace('p1.html', 'p' . $i . '.html', $url);
                $this->adapter_goutte->sendRequest($url);
            }

            $exam_names = $this->adapter_goutte->getText($exam_css_selector);
            $exam_urls = $this->adapter_goutte->getHrefAttr($exam_css_selector);
            $exam_types = $this->adapter_goutte->getAttrByName($exam_type_css_selector, 'class');
            foreach ($exam_names as $exam_key => $exam_name)
            {
                $exam_id = str_replace(['/s', '.html'], '', $exam_urls[$exam_key]);
                $download_url = sprintf($download_url_format, $exam_id);
                $insert_data['zz_title'] = $exam_name;
                $insert_data['zz_download_url'] = $download_url;
                $insert_data['zz_file_type'] = trim(str_replace('it_1 ', '', $exam_types[$exam_key]));
                $insert_data['zz_content'] = '';
                $insert_data['zz_url'] = $this->_url_prefix . $exam_urls[$exam_key];
                $this->adapter_db->insert($this->table_names['exam'], $insert_data);
            }
        }
    }

    private function _getLoginInfoFromMobile()
    {
        $account = Util::getAccount('zxxk');
        if ($account == null)
        {
            return ['', ''];
        }

        $login_mobile_url = 'http://user.zxxk.com/Login_m.aspx';

        $login_data = [
            'ComeUrl' => urlencode('http://www.zxxk.com/m/login/'),
            'UserName' => $account['username'],
            'UserPassword' => $account['password'],
        ];

        return [
            $login_mobile_url,
            $login_data
        ];
    }

    private function _loginAndDownloadExam($login_url, array $login_data, $save_dir)
    {
        //init curl
        $ch = curl_init();

        //Set the URL to work with
        curl_setopt($ch, CURLOPT_URL, $login_url);

        // ENABLE HTTP POST
        curl_setopt($ch, CURLOPT_POST, 1);

        //Set the post parameters
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($login_data));

        //Handle cookies for the login
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookie_path);

        //Setting CURLOPT_RETURNTRANSFER variable to 1 will force cURL
        //not to print out the results of its query.
        //Instead, it will return the results as a string return value
        //from curl_exec() instead of the usual true/false.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //execute the request (the login)
        $result = curl_exec($ch);

        //the login is now done and you can continue to get the private content.
        if(!file_exists($save_dir))
        {
            mkdir($save_dir, 0777, true);
        }

        $download_arr = $this->_downloadDoc();
        foreach ($download_arr as $filename => $download_url)
        {
            curl_setopt($ch, CURLOPT_URL, $download_url);
            $doc_download_content = curl_exec($ch);
            $preg_doc_url = '/href="([^"]+)"/';
            $is_match = preg_match($preg_doc_url, $doc_download_content, $matches);
            if ($is_match)
            {
                $doc_download_url = str_replace([';', '&amp'], ['&', ''], $matches[1]);
                curl_setopt($ch, CURLOPT_URL, $doc_download_url);
                $doc_content = curl_exec($ch);
                file_put_contents($save_dir . $filename, $doc_content);

                $sleep_second = rand(1, 10);
                sleep($sleep_second);
            }
        }

        echo 'finished';
        exit;
    }

    private function _downloadDoc()
    {
        $fetch_fields = [
            'zz_id',
            'zz_download_url',
            'zz_file_type',
        ];
        $where = [
            'AND' => [
                'zz_status[=]' => 1,
                'zz_content[=]' => '',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        $download_arr = [];
        if (!empty($download_data))
        {
            foreach ($download_data as $download_value)
            {
                $download_arr[$download_value['zz_id'] . '.' . $download_value['zz_file_type']] = $download_value['zz_download_url'];
            }
        }

        return $download_arr;
    }

    private function _getPrid($province_name)
    {
        $prid = $this->adapter_db->selectOne(
            $this->table_names['province'],
            'pr_id',
            [
                'LIKE' => [
                    'pr_name%' => $province_name
                ],
            ]
        );

        return $prid;
    }

}

$test = new Zxxk_zuhe();
$test->run();