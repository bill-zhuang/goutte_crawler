<?php

require_once 'Crawl_Base.php';
class Zxxk_sj extends Crawl_Base
{
    private $_cookie_path;

    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://sj.zxxk.com/',
        ];
        $this->table_names = [
            'province' => 'province',
            'grade' => 'grade',
            'course' => 'course',
            'exam' => 'zxxk_sj'
        ];
        $this->_cookie_path = '../cookie/zxxk_sj_cookie.txt';
        $this->url_prefix = 'http://sj.zxxk.com';
    }

    public function run()
    {
        $this->_getCourseUrls();

        $save_dir = 'D:/zxxk_sj/';
        //list ($login_url, $login_data) = $this->_getLoginInfoFromPC();
        list ($login_url, $login_data) = $this->_getLoginInfoFromMobile();
        $this->_loginAndDownloadExam($login_url, $login_data, $save_dir);
    }

    private function _getCourseUrls()
    {
        $course_css_selector = 'div.wrapper.bot10.nav ul#nav.clearfix li a';
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $course_names = $this->adapter_goutte->getText($course_css_selector);
        $course_urls = $this->adapter_goutte->getHrefAttr($course_css_selector);
        //--> '/s-7-0-0-0-0--0-0-0-0--0-0-2-20-13-1-0---p1.html',  the number 7 & 2
        $preg_grad_id = '/(\/s)\-(\d+)(\-\d+\-\d+\-\d+\-\d+\-\-\d+\-\d+\-\d+\-\d+\-\-\d+\-\d+)\-(\d+)/';
        //--> '/s-0-2-0-0-0--0-0-0-0--0-0-0-20-13-1-0---p1.html', the number 2
        $preg_province_id = '/(\/s\-\d+)\-(\d+)/';
        foreach ($course_urls as $course_key => $course_url)
        {
            if ($course_url != '/Default.aspx')
            {
                $coid = $this->_getCoid($course_names[$course_key]);
                $course_url = $this->url_prefix . $course_url;
                $this->adapter_goutte->sendRequest($course_url);
                $categories = $this->_getGradesProvincesYears();
                $free_charge_url = $this->_getExamMoney();
                if ($free_charge_url != '')
                {
                    foreach ($categories['grade']['names'] as $grade_name_key => $grade_name)
                    {
                        $grid = $this->_getGrid($grade_name);
                        foreach ($categories['province']['names'] as $province_name_key => $province_name)
                        {
                            $prid = $this->_getPrid($province_name);
                            $visit_url = $free_charge_url;
                            $is_match_grid = preg_match($preg_grad_id, $categories['grade']['urls'][$grade_name_key], $grid_matches);
                            $is_match_prid = preg_match($preg_province_id, $categories['province']['urls'][$province_name_key], $prid_matches);
                            if ($is_match_grid && $is_match_prid)
                            {
                                $visit_url = preg_replace($preg_grad_id, '\1-' . $grid_matches[2] . '\3-' . $grid_matches[4], $visit_url);
                                $visit_url = preg_replace($preg_province_id, '\1-' . $prid_matches[2], $visit_url);

                                $visit_url = $this->url_prefix . $visit_url;
                                //crawl exam paper
                                $this->adapter_goutte->sendRequest($visit_url);
                                $total_pages = $this->_getTotalPage();
                                $this->_crawlExamPaper($visit_url, $total_pages, $prid, $grid, $coid);
                            }
                        }
                    }
                }
            }
        }
    }

    private function _getGradesProvincesYears()
    {
        $category_css_selector = 'div.select_con.navbox div.soft_bk.soft_bk_bottom div.line div.left.rt a';
        $names = $this->adapter_goutte->getText($category_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($category_css_selector);
        $category_info = [
            'grade' => [
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
                    $category_info['grade']['names'][] = $name;
                    $category_info['grade']['urls'][] = $urls[$key];
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

    private function _crawlExamPaper($url, $total_pages, $prid, $grid, $coid)
    {
        $exam_css_selector = 'div.list_top a.left';
        $exam_type_css_selector = 'div.list_top div.it_1';
        $download_url_format = 'http://download.zxxk.com/?UrlID=29&InfoID=%s';
        $insert_data = [
            'gr_id' => $grid,
            'co_id' => $coid,
            'pr_id' => $prid,
            'zs_status' => 1,
            'zs_create_time' => date('Y-m-d H:i:s'),
            'zs_update_time' => date('Y-m-d H:i:s')
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
                $insert_data['zs_title'] = $exam_name;
                $insert_data['zs_download_url'] = $download_url;
                $insert_data['zs_file_type'] = trim(str_replace('it_1 ', '', $exam_types[$exam_key]));
                $insert_data['zs_content'] = '';
                $insert_data['zs_url'] = $this->url_prefix . $exam_urls[$exam_key];
                $this->adapter_db->insert($this->table_names['exam'], $insert_data);
            }
        }
    }

    private function _getLoginInfoFromPC()
    {
        $account = Util::getAccount('zxxk');
        if ($account == null)
        {
            return ['', ''];
        }

        $login_url = 'http://passport.zxxk.com/Login.aspx';

        $login_data = [
            'ComeUrl' => urlencode('http://sj.zxxk.com/'),
            'UserName' => $account['username'],
            'UserPassword' => $account['password'],
            'CheckCode' => $this->_getCaptcha(),
        ];

        return [
            $login_url,
            $login_data
        ];
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

    private function _getCaptcha()
    {
        $captcha_url = 'http://passport.zxxk.com/RanImg.aspx';
        $captcha_image_content = file_get_contents($captcha_url);
        file_put_contents($captcha_image_content, 'captcha.gif');
        //extract R channel & set threshold at 80, the captcha will clear
        //todo extract captcha
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
            'zs_id',
            'zs_download_url',
            'zs_file_type',
        ];
        $where = [
            'AND' => [
                'zs_status[=]' => 1,
                'zs_content[=]' => '',
            ],
        ];

        $download_data = $this->adapter_db->selectAll($this->table_names['exam'], $fetch_fields, $where);
        if (!empty($download_data))
        {
            $download_arr = [];
            foreach ($download_data as $download_value)
            {
                $download_arr[$download_value['zs_id']] = $download_value['zs_download_url'];
            }
        }

        return $download_arr;
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

$test = new Zxxk_sj();
$test->run();