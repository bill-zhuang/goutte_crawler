<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午3:18
 */

require_once '../models/Goutte_Crawl.php';
require_once '../models/DBTableFactory.php';
class Izaojiao
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
            'http://www.izaojiao.com/'
        ];
        $this->_table_names = [
            'branch' => 'izaojiao_branch',
            'institution' => 'izaojiao_institution',
            'institution_info' => 'izaojiao_institution_info'
        ];
    }

    public function run()
    {
        $this->_crawlInstitution();
    }

    private function _crawlInstitution()
    {
        $institution_branch_css_selector = 'div.footer div.main.foot_citys a';
        $institution_total_num_css_selector = 'div.center.m_b20 a.page';

        $this->_adapter_goutte->sendRequest($this->_crawl_urls[0]);
        $institution_branch_names = $this->_adapter_goutte->getText($institution_branch_css_selector);
        $institution_branch_urls = $this->_adapter_goutte->getHrefAttr($institution_branch_css_selector);

        if (!empty($institution_branch_names))
        {
            foreach ($institution_branch_names as $branch_key => $institution_branch_name)
            {
                $institution_branch_url = str_replace($this->_crawl_urls[0], $this->_crawl_urls[0] . 'jigou/',
                    $institution_branch_urls[$branch_key]);
                $institution_branch_url = $institution_branch_url . '/all';
                $brand_data = [
                    'ib_url' => $institution_branch_url,
                    'ib_name' => $institution_branch_name
                ];
                $this->_adapter_db->insert($this->_table_names['branch'], $brand_data);
                $this->_adapter_goutte->sendRequest($institution_branch_url);
                $total_pages = $this->_adapter_goutte->getText($institution_total_num_css_selector);

                //page 1
                $this->_insertInstitution($institution_branch_name);

                if (!empty($total_pages))
                {
                    //page 2->end if exit
                    $total_page = $total_pages[count($total_pages) -2];
                    for ($i = 1; $i < $total_page; $i++)
                    {
                        $url = $institution_branch_url . '/p' . $i;
                        $this->_adapter_goutte->sendRequest($url);
                        $this->_insertInstitution($institution_branch_name);
                    }
                }
            }
        }
    }

    private function _insertInstitution($institution_branch_name)
    {
        $institution_css_selector = 'div.sou div.con ul.nr li.bt.clearfix h2 a';
        $institution_names = $this->_adapter_goutte->getText($institution_css_selector);
        $institution_urls = $this->_adapter_goutte->getHrefAttr($institution_css_selector);

        $institution_url = '';
        foreach ($institution_names as $key => $institution_name)
        {
            $institution_url = 'http://www.izaojiao.com' . $institution_urls[$key];
            $institution_data = [
                'ii_url' => $institution_url,
                'ii_name' => $institution_name,
                'ii_branch' => $institution_branch_name,
                'ii_status' => 1
            ];
            $this->_adapter_db->insert($this->_table_names['institution'], $institution_data);
            $this->_insertInstitutionInfo($institution_url, $institution_name, $institution_branch_name);
        }
    }

    private function _insertInstitutionInfo($institution_url, $institution_name, $institution_branch_name)
    {
        $logo_css_selector = 'div.w_1000 div.g_l ul.g_fc li a img';
        $info1_css_selector = 'div.w_1000 div.g_r div.jie ul li a';
        $info2_css_selector = 'div.w_1000 div.g_r div.jie ul li span';
        //$phone_css_selector = 'div.w_1000 div.g_r div.jie ul li div span';
        //$website_css_selector = 'div.w_1000 div.g_r div.jie ul li span.fl a';
        $intro_css_selector = 'div.w_1000 div.main_left div.hcon.p_15 div';

        $this->_adapter_goutte->sendRequest($institution_url);
        $logo_info = $this->_adapter_goutte->getImageSrcAttr($logo_css_selector);
        $logo = 'http://www.izaojiao.com' . $logo_info[0];
        $info1 = $this->_adapter_goutte->getText($info1_css_selector);
        $info2 = $this->_adapter_goutte->getText($info2_css_selector);

        $type = [];
        $age = [];
        $area = [];
        $char = '';
        $info1_len = count($info1);
        foreach ($info1 as $key => $value)
        {
            $char = mb_substr($value, 0, 1);
            if (!is_numeric($char) && empty($age))
            {
                $type[] = $value;
            }
            else if (is_numeric($char))
            {
                $age[] = $value;
            }
            else if (!is_numeric($char) && !empty($age) && $key < ($info1_len - 2))
            {
                $area[] = $value;
            }
            else
            {
                break;
            }
        }

        $address = $info2[4];
        $phone = $info2[6];
        $website = $info1[$info1_len - 1];

        $this->_adapter_goutte->sendRequest($institution_url . '/intro');
        $intro_info = $this->_adapter_goutte->getText($intro_css_selector);
        $intro = $intro_info[0];

        $institution_info_data = [
            'iii_branch' => $institution_branch_name,
            'iii_name' => $institution_name,
            'iii_logo' => $logo,
            'iii_type' => implode(',', $type),
            'iii_age' => implode(',', $age),
            'iii_area' => implode(',', $area),
            'iii_address' => $address,
            'iii_phone' => $phone,
            'iii_intro' => $intro,
            'iii_website' => $website,
            'iii_url' => $institution_url
        ];
        $this->_adapter_db->insert($this->_table_names['institution_info'], $institution_info_data);
    }
}

$test = new Izaojiao();
$test->run();