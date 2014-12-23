<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午4:03
 */

require_once '../models/Goutte_Crawl.php';
require_once '../models/DBTableFactory.php';
class Boqqi
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
        set_time_limit(0);
        $this->_adapter_goutte = new Goutte_Crawl();
        $this->_adapter_db = new DBTableFactory();
        $this->_crawl_urls = [

        ];
        $this->_table_names = [
            'city' => 'boqqi_city',
            'district' => 'boqqi_district',
            'institution' => 'boqqi_institution',
            'institution_info' => 'boqqi_institution_info'
        ];
    }

    public function run()
    {
        $this->_crawlInstitution();
    }

    private function _crawlInstitution()
    {
        $all_city_data = $this->_getAllCitiy();
        foreach ($all_city_data['name'] as $city_key => $city_name)
        {
            $city_data = [
                'bc_name' => $city_name,
                'bc_url' => $all_city_data['url'][$city_key]
            ];
            $this->_adapter_db->insert($this->_table_names['city'], $city_data);
            $district_category_data = $this->_getDistrictAndCategory($all_city_data['url'][$city_key]);
            //
            $category_ids = [];
            foreach ($district_category_data['category'] as $category_value)
            {
                $category_id = $this->_getCategoryID($category_value['url']);
                if ($category_id != 0)
                {
                    $category_ids[] = $category_id;
                }
            }

            foreach ($district_category_data['district'] as $district_value)
            {
                $visit_url = $district_value['url'];
                $district_data = [
                    'bd_name' => $district_value['name'],
                    'bd_city' => $city_name,
                    'bd_url' => $district_value['url']
                ];
                $this->_adapter_db->insert($this->_table_names['district'], $district_data);

                if (!empty($category_ids))
                {
                    foreach ($category_ids as $category_key => $category_id)
                    {
                        $actual_visit_url = str_replace('list-0', 'list-' . $category_id, $visit_url);
                        $total_data = $this->_getInstitutionPageNum($actual_visit_url);
                        $total_page = 1;
                        if (!empty($total_data))
                        {
                            $total_page = $total_data[count($total_data) - 2];
                        }
                        $all_institution_data = $this->_getInstitution($actual_visit_url, $total_page);
                        if (isset($all_institution_data['name']))
                        {
                            foreach ($all_institution_data['name'] as $institution_key => $institution_name)
                            {
                                $institution_data = [
                                    'bi_city' => $city_name,
                                    'bi_district' => $district_value['name'],
                                    'bi_status' => 1,
                                    'bi_name' => $institution_name,
                                    'bi_url' => $all_institution_data['url'][$institution_key]
                                ];
                                $this->_adapter_db->insert($this->_table_names['institution'], $institution_data);
                                $institution_info = $this->_getInstitutionInfo($all_institution_data['url'][$institution_key]);
                                //address phone detail
                                if (!empty($institution_info))
                                {
                                    $institution_info_data = [
                                        'bii_city' => $city_name,
                                        'bii_district' => $district_value['name'],
                                        'bii_status' => 1,
                                        'bii_name' => $institution_name,
                                        'bii_url' => $all_institution_data['url'][$institution_key],
                                        'bii_address' => $institution_info['address'],
                                        'bii_phone' => $institution_info['phone'],
                                        'bii_opentime' => $institution_info['opentime'],
                                        'bii_tags' => $institution_info['tags'] . ' ' . $district_category_data['category'][$category_key]['name'],
                                        'bii_intro' => isset($institution_info['detail'][0]) ? $institution_info['detail'][0] : '',
                                        'bii_logo' => isset($institution_info['logo'][0]) ? $institution_info['logo'][0] : ''
                                    ];
                                    $this->_adapter_db->insert($this->_table_names['institution_info'], $institution_info_data);
                                }
                            }
                        }
                    }
                }
                else
                {
                    $total_data = $this->_getInstitutionPageNum($visit_url);
                    $total_page = 1;
                    if (!empty($total_data))
                    {
                        $total_page = $total_data[count($total_data) - 2];
                    }
                    $all_institution_data = $this->_getInstitution($visit_url, $total_page);
                    if (isset($all_institution_data['name']))
                    {
                        foreach ($all_institution_data['name'] as $institution_key => $institution_name)
                        {
                            $institution_data = [
                                'bi_city' => $city_name,
                                'bi_district' => $district_value['name'],
                                'bi_status' => 1,
                                'bi_name' => $institution_name,
                                'bi_url' => $all_institution_data['url'][$institution_key]
                            ];
                            $this->_adapter_db->insert($this->_table_names['institution'], $institution_data);
                            $institution_info = $this->_getInstitutionInfo($all_institution_data['url'][$institution_key]);
                            //address phone detail
                            if (!empty($institution_info))
                            {
                                $institution_info_data = [
                                    'bii_city' => $city_name,
                                    'bii_district' => $district_value['name'],
                                    'bii_status' => 1,
                                    'bii_name' => $institution_name,
                                    'bii_url' => $all_institution_data['url'][$institution_key],
                                    'bii_address' => $institution_info['address'],
                                    'bii_phone' => $institution_info['phone'],
                                    'bii_opentime' => $institution_info['opentime'],
                                    'bii_tags' => $institution_info['tags'],
                                    'bii_intro' => isset($institution_info['detail'][0]) ? $institution_info['detail'][0] : '',
                                    'bii_logo' => isset($institution_info['logo'][0]) ? $institution_info['logo'][0] : ''
                                ];
                                $this->_adapter_db->insert($this->_table_names['institution_info'], $institution_info_data);
                            }
                        }
                    }
                }
            }
        }
    }

    private function _getAllCitiy()
    {
        $url = 'http://vet.boqii.com/hospital/list.html';
        $city_xpath = 'div.city_area a';
        $this->_adapter_goutte->sendRequest($url);
        $data_content = [
            'name' => '',
            'url' => ''
        ];
        $data_content['name'] = $this->_adapter_goutte->getText($city_xpath);
        $data_content['url'] = $this->_adapter_goutte->getHrefAttr($city_xpath);
        $data_content['url'] = array_map(
            function($url) {
                if ($url[0] != '') {
                    return $url . 'hospital/list.html';
                }
            },
            $data_content['url']
        );
        /*print_r($data_content);
        exit;*/

        return $data_content;
    }

    private function _getDistrictAndCategory($url)
    {
        $district_xpath = 'div.leftBA dl dd a';
        $this->_adapter_goutte->sendRequest($url);
        $all_data_content = [
            'name' => '',
            'url' => ''
        ];
        $all_data_content['name'] = $this->_adapter_goutte->getText($district_xpath);
        $all_data_content['url'] = $this->_adapter_goutte->getHrefAttr($district_xpath);
        $all_data_content['name'] = array_map(
            function($name) {
                return preg_replace('/\d+/', '', trim($name));
            },
            $all_data_content['name']
        );
        /*print_r($all_data_content);
        exit;*/
        $beijing_url = 'http://vet.boqii.com/bj/hospital/list.html';
        $return_data = [
            'district' => [],
            'category' => []
        ];
        $category_flag = false;
        foreach ($all_data_content['name'] as $name_key => $name)
        {
            if ($name == '全部')
            {
                if ($url != $beijing_url)
                {
                    $category_flag = !$category_flag;
                }
                continue;
            }
            else
            {
                if ($category_flag)
                {
                    if ($name != '宠物医院')
                    {
                        $return_data['category'][] = [
                            'name' => $name,
                            'url' => $all_data_content['url'][$name_key]
                        ];
                    }
                }
                else
                {
                    $return_data['district'][] = [
                        'name' => $name,
                        'url' => $all_data_content['url'][$name_key]
                    ];
                }
            }
        }
        /*print_r($return_data);
        exit;*/

        return $return_data;
    }

    private function _getCategoryID($category_url)
    {
        //http://vet.boqii.com/hz/hospital/list-1994-0-0.html
        $id = 0;
        $preg_district = '/(\d+)/';
        $is_match = preg_match_all($preg_district, $category_url, $matches);
        if ($is_match > 0)
        {
            $id = $matches[1][0];
        }

        return $id;
    }

    private function _getInstitutionPageNum($url)
    {
        $page_xpath = 'div.showpage a';
        $this->_adapter_goutte->sendRequest($url);
        $page_content = $this->_adapter_goutte->getText($page_xpath);
        /*print_r($page_content);
        exit;*/

        return $page_content;
    }

    private function _getInstitution($url, $total_page)
    {
        $institution_name_xpath = 'div.l_list div.l_listL dl dt a';
        $institution_data = [
            'name' => [],
            'url' => []
        ];
        for ($i = 1; $i <= $total_page; $i++)
        {
            if ($i != 1)
            {
                $actual_url = str_replace('.html', '-' . $i . '.html', $url);
                $this->_adapter_goutte->sendRequest($actual_url);
            }

            $institution_data['name'] = $this->_adapter_goutte->getText($institution_name_xpath);
            $institution_data['url'] = $this->_adapter_goutte->getHrefAttr($institution_name_xpath);
            /*print_r(array_filter($institution_data));
            exit;*/

            $institution_data = array_filter($institution_data);
        }

        return $institution_data;
    }

    private function _getInstitutionInfo($url)
    {
        $info_xpath = 'div.det_rbox dl';
        $detail_xpath = 'div.detB_con p';
        $logo_xpath = 'div.det_img img';
        $this->_adapter_goutte->sendRequest($url);
        $data = [
            'address' => '',
            'phone' => '',
            'opentime' => '',
            'tags' => '',
            'detail' => '',
            'logo' => ''
        ];
        $info = $this->_adapter_goutte->getText($info_xpath);
        list(, $data['address']) = explode('：', $info[1]);
        list(, $data['phone']) = explode('：', $info[2]);
        list(, $data['opentime']) = explode('：', $info[4]);
        list(, $data['tags']) = explode('：', $info[5]);

        $data['detail'] = $this->_adapter_goutte->getText($detail_xpath);
        $data['logo'] = $this->_adapter_goutte->getImageSrcAttr($logo_xpath);
        /*print_r($data);
        exit;*/

        return $data;
    }
}

$test = new Boqqi();
$test->run();