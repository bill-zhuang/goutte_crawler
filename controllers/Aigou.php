<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午3:48
 */

require_once 'Crawl_Base.php';
class Aigou extends Crawl_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->table_names = [
            'province' => 'aigou_province',
            'hospital' => 'aigou_hospital',
            'hospital_info' => 'aigou_hospital_info'
        ];
    }

    public function run()
    {
        $this->_crawlHospital();
    }

    private function _crawlHospital()
    {
        $all_province_data = $this->_getAllProvince();
        foreach ($all_province_data['name'] as $province_key => $province_name)
        {
            $province_data = [
                'ap_name' => $province_name
            ];
            $this->adapter_db->insert($this->table_names['province'], $province_data);
            $total_data = $this->_getHospitalPageNum($all_province_data['url'][$province_key]);
            $total_page = 1;
            if (!empty($total_data))
            {
                $total_page = $total_data[count($total_data) - 2];
            }
            $all_hospital_data = $this->_getHospital($all_province_data['url'][$province_key], $total_page);
            foreach ($all_hospital_data['name'] as $hospital_key => $hospital_name)
            {
                $hospital_data = [
                    'ah_province' => $province_name,
                    'ah_status' => 1,
                    'ah_name' => $hospital_name,
                    'ah_url' => $all_hospital_data['url'][$hospital_key]
                ];
                $this->adapter_db->insert($this->table_names['hospital'], $hospital_data);
                $hospital_info = $this->_getHospitalInfo($all_hospital_data['url'][$hospital_key]);
                //address phone detail
                if (!empty($hospital_info))
                {
                    $hospital_info_data = [
                        'ahi_province' => $province_name,
                        'ahi_status' => 1,
                        'ahi_name' => $hospital_name,
                        'ahi_url' => $all_hospital_data['url'][$hospital_key],
                        'ahi_address' => isset($hospital_info['address'][0]) ?
                                str_replace('详细地址：', '', $hospital_info['address'][0]) : '',
                        'ahi_phone' => isset($hospital_info['phone'][0]) ?
                                str_replace('联系方式：', '', $hospital_info['phone'][0]) : '',
                        'ahi_intro' => isset($hospital_info['detail'][0]) ? $hospital_info['detail'][0] : ''
                    ];
                    $this->adapter_db->insert($this->table_names['hospital_info'], $hospital_info_data);
                }
            }
        }
    }

    private function _getAllProvince()
    {
        $province_css_selector = 'div.city-all ul.city-list li a';
        $url = 'http://hospital.aigou.com/hospitallist_1__.html';
        $this->adapter_goutte->sendRequest($url);
        $hospital_data = [
            'name' => '',
            'url' => ''
        ];
        $hospital_data['name'] = $this->adapter_goutte->getText($province_css_selector);
        $hospital_data['url'] = $this->adapter_goutte->getHrefAttr($province_css_selector);
        $hospital_data['url'] = array_map(
            function($url){
                if($url != ''){
                    return 'http://hospital.aigou.com/' . $url;
                }
            },
            $hospital_data['url']
        );
        /*print_r($hospital_data);
        exit;*/

        return $hospital_data;
    }

    private function _getHospitalPageNum($province_url)
    {
        $page_css_selector = 'div.page-nav ul li a';
        $this->adapter_goutte->sendRequest($province_url);
        $page_content = $this->adapter_goutte->getText($page_css_selector);
        /*print_r($page_content);
        exit;*/

        return $page_content;
    }

    private function _getHospital($url, $total_page)
    {
        $hospital_name_css_selector = 'div.wrap-left ul li.bold.fs14 a';
        $data = array();
        for ($i = 1; $i <= $total_page; $i++)
        {
            if ($i != 1)
            {
                $url = str_replace('_1_', '_' . $i . '_', $url);
                $this->adapter_goutte->sendRequest($url);
            }

            $hospital_data = [
                'name' => '',
                'url' => ''
            ];
            $hospital_data['name'] = $this->adapter_goutte->getText($hospital_name_css_selector);
            $hospital_data['url'] = $this->adapter_goutte->getHrefAttr($hospital_name_css_selector);
            $hospital_data['url'] = array_map(
                function($url){
                    if($url != ''){
                        return 'http://hospital.aigou.com/' . $url;
                    }
                },
                $hospital_data['url']
            );
            /*print_r($hospital_data);
            exit;*/

            $data = array_merge($data, $hospital_data);
        }

        return $data;
    }

    private function _getHospitalInfo($hospital_url)
    {
        $address_css_selector = 'div.table div.tr div.td p.mt5';
        $phone_css_selector = 'div.table div.tr div.td p.mt8';
        $detail_css_selector = 'div.table div.tr div.td div.mt10.t-i.lh15.fs14';
        $this->adapter_goutte->sendRequest($hospital_url);
        $data = [
            'address' => '',
            'phone' => '',
            'detail' => ''
        ];
        $data['address'] = $this->adapter_goutte->getText($address_css_selector);
        $data['phone'] = $this->adapter_goutte->getText($phone_css_selector);
        $data['detail'] = $this->adapter_goutte->getText($detail_css_selector);
        /*print_r($data);
        exit;*/

        return $data;
    }
}

$test = new Aigou();
$test->run();