<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午3:05
 */

require_once '../models/Goutte_Crawl.php';
require_once '../models/DBTableFactory.php';
class Guahao
{
    /**
     * @var Goutte_Crawl
     */
    private $_adapter_goutte;
    /**
     * @var DBTableFactory
     */
    private $_adapter_db;
    private $_crawl_urls;
    private $_table_names;

    public function __construct()
    {
        $this->_adapter_goutte = new Goutte_Crawl();
        $this->_adapter_db = new DBTableFactory();
        $this->_crawl_urls = [

        ];
        $this->_table_names = [
            'province' => 'guahao_province',
            'city' => 'guahao_city',
            'hospital' => 'guahao_hospital',
            'hospital_info' => 'guahao_hospital_info'
        ];
    }

    public function run()
    {
        $this->_crawlHospital();
    }

    private function _crawlHospital()
    {
        $location_data = $this->_getAllProvinceAndCity();
        foreach ($location_data as $location_value)
        {
            $prid = $location_value['areaId'];
            $province_name = $location_value['areaName'];
            $province_data = [
                'gp_name' => $province_name,
                'gp_province_id' => $prid
            ];
            $this->_adapter_db->insert($this->_table_names['province'], $province_data);
            foreach ($location_value['cities'] as $city_value)
            {
                $ctid = $city_value['areaId'];
                $city_name = $city_value['areaName'];
                if ($ctid != 'all')
                {
                    $city_data = [
                        'gc_name' => $city_name,
                        'gc_city_id' => $ctid,
                        'gc_province_id' => $prid
                    ];
                    $this->_adapter_db->insert($this->_table_names['city'], $city_data);

                    $encode_province_name = rawurlencode($province_name);
                    $encode_city_name = rawurlencode($city_name);

                    $total_data = $this->_getHospitalPageNum($prid, $encode_province_name, $ctid, $encode_city_name);
                    if (!empty($total_data) && $total_data[0] > 0)
                    {
                        $all_hospital_data = $this->_getHospital($prid, $encode_province_name, $ctid, $encode_city_name, $total_data[0]);
                        foreach ($all_hospital_data['name'] as $hospital_key => $hospital_name)
                        {
                            $hospital_url = $all_hospital_data['url'][$hospital_key];
                            $hospital_data = [
                                'gh_province_id' => $prid,
                                'gh_city_id' => $ctid,
                                'gh_status' => 1,
                                'gh_name' => $hospital_name,
                                'gh_url' => $hospital_url
                            ];
                            $this->_adapter_db->insert($this->_table_names['hospital'], $hospital_data);
                            $hospital_id = str_replace('http://www.guahao.com/hospital/', '', $hospital_url);
                            $hospital_info = $this->_getHospitalInfo($hospital_id);
                            //address phone detail
                            if (!empty($hospital_info))
                            {
                                $hospital_info_data = [
                                    'ghi_province_id' => $prid,
                                    'ghi_city_id' => $ctid,
                                    'ghi_status' => 1,
                                    'ghi_name' => $hospital_name,
                                    'ghi_url' => $hospital_url,
                                    'ghi_address' => isset($hospital_info['address'][0]) ?
                                        preg_replace('/\r\n.+/', '', $hospital_info['address'][0]) : '',
                                    'ghi_phone' => isset($hospital_info['phone'][0]) ? $hospital_info['phone'][0] : '',
                                    'ghi_intro' => isset($hospital_info['detail'][0]) ? $hospital_info['detail'][0] : ''
                                ];
                                $this->_adapter_db->insert($this->_table_names['hospital_info'], $hospital_info_data);
                            }
                        }
                    }
                }
            }
        }
    }

    private function _getAllProvinceAndCity()
    {
        $data = [];
        $url = 'http://img.guahao.cn/common/js/plugins/jquery-gl-areapicker.js';
        $content = file_get_contents($url);
        if ($content !== false)
        {
            $regex = '/var\s+r=(\[\{[^\(]+)/';
            $is_match = preg_match($regex, $content, $matches);
            if ($is_match > 0)
            {
                $str = str_replace(
                    ['areaId', 'areaName', 'cities'],
                    ['"areaId"', '"areaName"', '"cities"'],
                    $matches[1]
                );
                $data = json_decode(substr($str, 0, strlen($str) - 1), true);
                /*print_r($data);
                exit;*/
            }
        }

        return $data;
    }

    private function _getAllProvince()
    {
        $url = 'http://www.guahao.com/json/white/area/provinces';
        $content = json_decode(file_get_contents($url), true);
        /*print_r($content);
        exit;*/

        return $content;
    }

    private function _getProvinceCity($prid)
    {
        $url = 'http://www.guahao.com/json/white/area/citys?provinceId=' . $prid;
        $content = json_decode(file_get_contents($url), true);
        /*print_r($content);
        exit;*/

        return $content;
    }

    private function _getHospitalPageNum($prid, $province_name, $ctid, $city_name)
    {
        $page_css_selector = 'div.other-info span.pd label';
        $url = 'http://www.guahao.com/hospital/areahospitals?sort=0&q=&pi=%s&p=%s&ci=%s&c=%s&pageNo=1';
        $url = sprintf($url, $prid, $province_name, $ctid, $city_name);
        $this->_adapter_goutte->sendRequest($url);
        $page_content = $this->_adapter_goutte->getText($page_css_selector);
        /*print_r($page_content);
        exit;*/

        return $page_content;
    }

    private function _getHospital($prid, $province_name, $ctid, $city_name, $total_page_num)
    {
        $hospital_name_css_selector = 'div.search-hos-info.g-clear dl dt a';
        $url = 'http://www.guahao.com/hospital/areahospitals?sort=0&q=&pi=%s&p=%s&ci=%s&c=%s&pageNo=%s';
        $data = [
            'name' => [],
            'url' => []
        ];
        for ($i = 1; $i <= $total_page_num; $i++)
        {
            if ($i != 1)
            {
                $this->_adapter_goutte->setFakeHeaderIP();
                $real_url = sprintf($url, $prid, $province_name, $ctid, $city_name, $i);
                $this->_adapter_goutte->sendRequest($real_url);
            }

            $name_content = $this->_adapter_goutte->getText($hospital_name_css_selector);
            $url_content = $this->_adapter_goutte->getHrefAttr($hospital_name_css_selector);
            /*print_r($name_content);
            print_r($url_content);
            exit;*/

            $data['name'] = array_merge($data['name'], $name_content);
            $data['url'] = array_merge($data['url'], $url_content);
        }

        return $data;
    }

    private function _getHospitalInfo($hospital_id)
    {
        $address_css_selector = 'p.introduce-ads span';
        $phone_css_selector = 'p.introduce-tel span';
        $url = 'http://www.guahao.com/hospital/' . $hospital_id;
        $this->_adapter_goutte->sendRequest($url);
        $data = [
            'address' => '',
            'phone' => '',
            'detail' => ''
        ];
        $data['address'] = $this->_adapter_goutte->getText($address_css_selector);
        $data['phone'] = $this->_adapter_goutte->getText($phone_css_selector);
        $data['detail'] = $this->_getHospitalDetail($hospital_id);

        return $data;
    }

    private function _getHospitalDetail($hospital_id)
    {
        $desc_css_selector = 'div.g-grid2-l div.hosp-info-mask div.info';
        $url = 'http://www.guahao.com/hospital/desc/' . $hospital_id;
        $this->_adapter_goutte->sendRequest($url);
        $address_content = $this->_adapter_goutte->getHtml($desc_css_selector);

        return $address_content;
    }
}

$test = new Guahao();
$test->run();