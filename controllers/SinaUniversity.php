<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-17
 * Time: 下午3:54
 */

require_once 'Crawl_Base.php';
class SinaUniversity extends Crawl_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://edu.sina.com.cn/gaokao/2013-07-01/1605387001.shtml?qq-pf-to=pcqq.c2c'
        ];
        $this->table_names = [
            'province' => 'sina_province',
            'university' => 'sina_university'
        ];
    }

    public function run()
    {
        $this->_crawlUniversity();
    }

    private function _crawlUniversity()
    {
        $province_university_data = $this->_getProvinceUniversityUrl();
        foreach ($province_university_data['name'] as $key => $province_name)
        {
            $province_data = [
                'sp_name' => $province_name
            ];
            $prid = $this->adapter_db->insert($this->table_names['province'], $province_data);
            if ($prid != 0)
            {
                $data_set = [
                    'su_name' => '',
                    'su_type' => '',
                    'sp_id' => $prid,
                    'su_status' => 1
                ];

                $university_data = $this->_getUniversityUnderProvince($province_university_data['url'][$key]);
                for ($i = 6, $len = count($university_data); $i < $len; $i += 5)
                {
                    $university_name = $university_data[$i + 1];
                    $university_level = $university_data[$i + 4];
                    $data_set['su_name'] = $university_name;
                    $data_set['su_type'] = $university_level;
                    $this->adapter_db->insert($this->table_names['university'], $data_set);
                }
            }
            else
            {
                //exit;
            }
        }

    }

    private function _getProvinceUniversityUrl()
    {
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $province_university_css_selector = '#artibody table tbody tr td div.STYLE1 a';
        $province_university_data = [
            'name' => '',
            'url' => ''
        ];
        $province_university_data['name'] = $this->adapter_goutte->getText($province_university_css_selector);
        $province_university_data['url'] = $this->adapter_goutte->getHrefAttr($province_university_css_selector);
        /*print_r($province_university_data);
        exit;*/
        return $province_university_data;
    }

    private function _getUniversityUnderProvince($url)
    {
        $this->adapter_goutte->sendRequest($url);
        $university_css_selector = '#artibody table tbody tr td';
        $university_data = $this->adapter_goutte->getText($university_css_selector);

        return $university_data;
    }
}

$test = new SinaUniversity();
$test->run();