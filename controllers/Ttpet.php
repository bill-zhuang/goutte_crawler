<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午4:19
 */

require_once 'Crawl_Base.php';
class Ttpet extends Crawl_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->table_names = [
            'main_category' => 'ttpet_main_category',
            'sub_category' => 'ttpet_sub_category',
            'pet' => 'ttpet_pet',
            'pet_info' => 'ttpet_pet_info'
        ];
        $this->url_prefix = 'http://www.ttpet.com';
    }

    public function run()
    {
        $this->_crawlPet();
    }

    private function _crawlPet()
    {
        $special_data = $this->_getSpeciesAndCategory();
        $key_map = [
            0 => ['start' => 0, 'end' => 2],
            1 => ['start' => 3, 'end' => 8],
            2 => ['start' => 9, 'end' => 14],
            3 => ['start' => 15, 'end' => 19],
            4 => ['start' => 20, 'end' => 25],
            5 => ['start' => 26, 'end' => 28],
        ];
        foreach ($special_data['category'] as $category_key => $category_value)
        {
            if ($category_key == 0 || $category_key == 5)
            {
                continue;
            }
            else
            {
                $main_category_data = [
                    'tmc_name' => $category_value
                ];
                $this->adapter_db->insert($this->table_names['main_category'], $main_category_data);
                foreach ($special_data['content'] as $content_key => $content_value)
                {
                    if ($content_key >= $key_map[$category_key]['start'] && $content_key <= $key_map[$category_key]['end'])
                    {
                        $sub_category_data = [
                            'tsc_main_category' => $category_value,
                            'tsc_name' => $content_value,
                            'tsc_url' => $special_data['url'][$content_key]
                        ];
                        $this->adapter_db->insert($this->table_names['sub_category'], $sub_category_data);
                        $total_page = 1;
                        $total_data = $this->_getSubcategoryPageNum($special_data['url'][$content_key]);
                        if (!empty($total_data))
                        {
                            $total_page = intval($total_data[0]);
                        }
                        $pet_data = $this->_getPet($special_data['url'][$content_key], $total_page, $category_value, $content_value);

                        //to many data, insert data in _getPet method
                        /*foreach ($pet_data as $pet_value)
                        {
                            $pet_data = [
                                'tp_main_category' => $category_value,
                                'tp_sub_category' => $content_value,
                                'tp_status' => 1,
                                'tp_name' => $pet_value['name'],
                                'tp_url' => $pet_value['url']
                            ];
                            $this->adapter_db->insert($this->table_names['pet'], $pet_data);
                            $pet_info = $this->_getPetInfo($pet_value['url']);
                            //pet detail
                            if (!empty($pet_info))
                            {
                                $pet_info_data = [
                                    'tpi_main_category' => $category_value,
                                    'tpi_sub_category' => $content_value,
                                    'tpi_status' => 1,
                                    'tpi_name' => $pet_value['name'],
                                    'tpi_url' => $pet_value['url'],
                                    'tpi_intro' => isset($pet_info[0]) ? $pet_info[0] : ''
                                ];
                                $this->adapter_db->insert($this->table_names['pet_info'], $pet_info_data);
                            }
                        }*/
                    }
                }
            }
        }
    }

    private function _getSpeciesAndCategory()
    {
        $url = 'http://www.ttpet.com/zixun/';
        $category_xpath = 'div.zxmenu dl dt a';
        $content_xpath = 'div.zxmenu dl dd a';
        $url_xpath = 'div.zxmenu dl dd a';
        $this->adapter_goutte->sendRequest($url);
        $category_content = $this->adapter_goutte->getText($category_xpath);
        $content_content = $this->adapter_goutte->getText($content_xpath);
        $url_content = $this->adapter_goutte->getHrefAttr($url_xpath);
        $url_content = array_map(
            function($url) {
                if (isset($url)) {
                    return $this->url_prefix . $url;
                }
            },
            $url_content
        );
        /*print_r($category_content);
        print_r($content_content);
        print_r($url_content);
        exit;*/

        return [
            'category' => $category_content,
            'content' => $content_content,
            'url' => $url_content
        ];
    }

    private function _getSubcategoryPageNum($url)
    {
        $page_xpath = 'div.wo_page a.last';
        $this->adapter_goutte->sendRequest($url);
        $page_content = $this->adapter_goutte->getText($page_xpath);
        $page_content = array_map(
            function($content) {
                return str_replace('.', '', $content);
            },
            $page_content
        );
        /*print_r($page_content);
        exit;*/

        return $page_content;
    }

    private function _getPet($url, $total_page, $category_value, $content_value)
    {
        $pet_name_xpath = 'div.p_pad ul.zixunlist dl dd a';
        $data = [];
        for ($i = 1; $i <= $total_page; $i++)
        {
            if ($i != 1)
            {
                $visit_url = str_replace('.html', '-' . $i . '.html', $url);
                $this->adapter_goutte->sendRequest($visit_url);
            }

            $all_pet_data = [
                'name' => '',
                'url' => ''
            ];
            $all_pet_data['name'] = $this->adapter_goutte->getText($pet_name_xpath);
            $all_pet_data['url'] = $this->adapter_goutte->getHrefAttr($pet_name_xpath);//print_r($all_pet_data);exit;
            $all_pet_data['url'] = array_map(
                function($url) {
                    if ($url != '') {
                        return $this->url_prefix . $url;
                    }
                },
                $all_pet_data['url']
            );
            /*print_r($all_pet_data);
            exit;*/

            //$data = array_merge($data, $all_pet_data);
            foreach ($all_pet_data['name'] as $pet_key => $pet_name)
            {
                $pet_data = [
                    'tp_main_category' => $category_value,
                    'tp_sub_category' => $content_value,
                    'tp_status' => 1,
                    'tp_name' => $pet_name,
                    'tp_url' => $all_pet_data['url'][$pet_key]
                ];
                $this->adapter_db->insert($this->table_names['pet'], $pet_data);
                $pet_info = $this->_getPetInfo($all_pet_data['url'][$pet_key]);
                //pet detail
                if (!empty($pet_info))
                {
                    $pet_info_data = [
                        'tpi_main_category' => $category_value,
                        'tpi_sub_category' => $content_value,
                        'tpi_status' => 1,
                        'tpi_name' => $pet_name,
                        'tpi_url' => $all_pet_data['url'][$pet_key],
                        'tpi_intro' => isset($pet_info[0]) ? $pet_info[0] : ''
                    ];
                    $this->adapter_db->insert($this->table_names['pet_info'], $pet_info_data);
                }
            }
        }

        //return $data;
        return true;
    }

    private function _getPetInfo($pet_url)
    {
        $detail_xpath = 'div.p_pad div.p_text';
        $this->adapter_goutte->sendRequest($pet_url);
        $data = $this->adapter_goutte->getHtml($detail_xpath);
        /*print_r($data);
        exit;*/

        return $data;
    }
}

$test = new Ttpet();
$test->run();