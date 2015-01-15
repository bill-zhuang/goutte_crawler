<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午2:52
 */

require_once 'Crawl_Base.php';
class Babytree extends Crawl_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://www.babytree.com/community/hospital.php'
        ];
        $this->table_names = [
            'province' => 'babytree_province',
            'city' => 'babytree_city',
            'hospital' => 'babytree_hospital',
            'hospital_info' => 'babytree_hospital_info'
        ];
    }

    public function run()
    {
        $this->_crawlHospital();
    }

    private function _crawlHospital()
    {
        $hospital_css_selector = 'div#agegroup div.hospital-full-list div.list-body dl dd ul li a';
        $hospital_info_css_selector = 'div#group-information div.hospital-detail-more table tbody tr td';

        $this->_goutte_crawler = $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $provinces = $this->_getProvinces();
        if (!empty($provinces))
        {
            foreach ($provinces as $province_key => $province)
            {
                if ($province_key == 1000)
                {
                    continue;
                }

                $province_data = [
                    'bp_province_id' => $province_key,
                    'bp_name' => $province['name']
                ];
                $this->adapter_db->insert($this->table_names['province'], $province_data);
                foreach ($province['list'] as $city_key => $city)
                {
                    $city_data = [
                        'bc_province_id' => $province_key,
                        'bc_city_id' => $city_key,
                        'bc_name' => $city
                    ];
                    $this->adapter_db->insert($this->table_names['city'], $city_data);

                    $city_hospital_url = $this->crawl_urls[0] . '?loc=' . $city_key;
                    $this->adapter_goutte->sendRequest($city_hospital_url);
                    $hospital_names = $this->adapter_goutte->getText($hospital_css_selector);
                    $hospital_urls = $this->adapter_goutte->getHrefAttr($hospital_css_selector);
                    foreach ($hospital_names as $hospital_key => $hospital_name)
                    {
                        $hospital_name = preg_replace('/\(.+/', '', $hospital_name);
                        $hospital_data = [
                            'bh_city_id' => $city_key,
                            'bh_name' => $hospital_name,
                            'bh_province_id' => $province_key,
                            'bh_url' => $hospital_urls[$hospital_key],
                            'bh_status' => 1
                        ];
                        $this->adapter_db->insert($this->table_names['hospital'], $hospital_data);
                        $this->adapter_goutte->sendRequest($hospital_urls[$hospital_key]);

                        //hospital information
                        $hospital_content = $this->adapter_goutte->getText($hospital_info_css_selector);
                        $hospital_address = isset($hospital_content[0]) ?
                            mb_substr($hospital_content[0], 0, mb_strlen($hospital_content[0], 'utf-8') - 4, 'utf-8') : '';
                        $hospital_phone = isset($hospital_content[1]) ?
                            mb_substr($hospital_content[1], 0, mb_strlen($hospital_content[1], 'utf-8') - 7, 'utf-8') : '';
                        $hospital_intro = isset($hospital_content[2]) ?
                            mb_substr($hospital_content[2], 0, mb_strlen($hospital_content[2], 'utf-8') - 7, 'utf-8') : '';

                        $hospital_info_data = [
                            'bhi_city_id' => $city_key,
                            'bhi_name' => $hospital_name,
                            'bhi_url' => $hospital_urls[$hospital_key],
                            'bhi_address' => $hospital_address,
                            'bhi_phone' => trim($hospital_phone),
                            'bhi_intro' => $hospital_intro,
                            'bhi_status' => 1,
                        ];
                        $this->adapter_db->insert($this->table_names['hospital_info'], $hospital_info_data);
                    }
                    //print_r($hospital_names);print_r($hospital_urls);exit;
                }
            }
        }
    }

    private function _getProvinces()
    {
        $main_category = array();

        $regex = '/var\s+dropdown\s+=\s+([^;]+)/s';
        $html = $this->adapter_goutte->getWholeHtmlPage();
        $is_match = preg_match($regex, $html, $matches);
        if ($is_match > 0)
        {
            $main_category = json_decode($matches[1], true);
        }

        return $main_category;
    }
}

$test = new Babytree();
$test->run();