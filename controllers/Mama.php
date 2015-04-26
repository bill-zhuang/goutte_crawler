<?php

require_once 'Crawl_Base.php';
class Mama extends Crawl_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            'pregnant' => 'http://www.mama.cn/baby/zhoukan/huaiyun/{which_week}week',
            'born_first_month' => 'http://www.mama.cn/baby/zhoukan/yuer/{which_week}w',
            'born_remain_month' => 'http://www.mama.cn/baby/zhoukan/yuer/{which_month}m',
            'born_year_month' => 'http://www.mama.cn/baby/zhoukan/yuer/{which_year}y{which_month}m'
        ];
        $this->table_names = [
            'main_category' => 'mama_main_category',
            'sub_category' => 'mama_sub_category',
            'article' => 'mama_article'
        ];
    }

    public function run()
    {
        $this->_crawlPregnantTips();
        $this->_crawlBornFirstMonthTips();
        $this->_crawlBornRemainMonthsTips();
        $this->_crawlBornYearMonthsTips();
    }

    private function _crawlPregnantTips()
    {
        for ($i = 1; $i <= 40; $i++)
        {
            $url = str_replace('{which_week}', $i, $this->crawl_urls['pregnant']);
            $this->adapter_goutte->sendRequest($url);
            //main category
            $main_category_css_selector = 'div#headWeekNav.wrap.headWeekNav p em a.cur';
            $main_category = $this->adapter_goutte->getText($main_category_css_selector);
            if ($this->adapter_db->selectCount(
                    $this->table_names['main_category'],
                    ['mmc_name[=]' => $main_category[0]]
                ) == 0)
            {
                $main_category_data = [
                    'mmc_name' => $main_category[0]
                ];
                $this->adapter_db->insert($this->table_names['main_category'], $main_category_data);
            }
            //sub category
            $sub_category_css_selector = 'div.headWeekBox h1';
            $sub_category = $this->adapter_goutte->getText($sub_category_css_selector);//print_r($sub_category);exit;
            $sub_category_data = [
                'msc_name' => $sub_category[0],
                'msc_main_category' => $main_category[0],
                'msc_url' => $url
            ];
            $this->adapter_db->insert($this->table_names['sub_category'], $sub_category_data);
            //article
            $baby_title_css_selector = 'article#baobao p.mdtitle';
            $baby_content_css_selector = 'div#baobaoState2';
            $mom_title_css_selector = 'article#mama p.mdtitle';
            $mom_content_css_selector = 'article#mama div.cl section';
            $dad_title_css_selector = 'article#baba.cl p.mdtitle';
            $dad_content_css_selector = 'dl#babaList1.baobaoList';
            $shopping_title_css_selector = 'article#shopping.cl p.mdtitle';
            $shopping_content_css_selector = 'section.shoppingBox';
            $baby_title = $this->adapter_goutte->getText($baby_title_css_selector);
            $baby_content = $this->adapter_goutte->getHtml($baby_content_css_selector);
            $mom_title = $this->adapter_goutte->getText($mom_title_css_selector);
            $mom_content = $this->adapter_goutte->getHtml($mom_content_css_selector);
            $dad_title = $this->adapter_goutte->getText($dad_title_css_selector);
            $dad_content = $this->adapter_goutte->getHtml($dad_content_css_selector);
            $shopping_title = $this->adapter_goutte->getText($shopping_title_css_selector);
            $shopping_content = $this->adapter_goutte->getHtml($shopping_content_css_selector);

            $article_data = [
                'ma_sub_category' => $sub_category[0],
                'ma_main_category' => $main_category[0],
                'ma_title' => '',
                'ma_content' => '',
                'ma_url' => $url,
                'ma_status' => 1
            ];
            //baby
            $article_data['ma_title'] = $baby_title[0];
            $article_data['ma_content'] = $baby_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //mom
            $article_data['ma_title'] = $mom_title[0];
            $article_data['ma_content'] = $mom_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //dad
            $article_data['ma_title'] = $dad_title[0];
            $article_data['ma_content'] = $dad_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //shopping
            $article_data['ma_title'] = $shopping_title[0];
            $article_data['ma_content'] = $shopping_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
        }
    }

    private function _crawlBornFirstMonthTips()
    {
        for ($i = 1; $i <= 4; $i++)
        {
            $url = str_replace('{which_week}', $i, $this->crawl_urls['born_first_month']);
            $this->adapter_goutte->sendRequest($url);
            //main category
            $main_category_css_selector = 'div#headWeekNav.wrap.headWeekNav p em a.cur';
            $main_category = $this->adapter_goutte->getText($main_category_css_selector);
            if ($this->adapter_db->selectCount(
                    $this->table_names['main_category'],
                    ['mmc_name[=]' => $main_category[0]]
                ) == 0)
            {
                $main_category_data = [
                    'mmc_name' => $main_category[0]
                ];
                $this->adapter_db->insert($this->table_names['main_category'], $main_category_data);
            }
            //sub category
            $sub_category_css_selector = 'div.headWeekBox h1';
            $sub_category = $this->adapter_goutte->getText($sub_category_css_selector);
            $sub_category_data = [
                'msc_name' => $sub_category[0],
                'msc_main_category' => $main_category[0],
                'msc_url' => $url
            ];
            $this->adapter_db->insert($this->table_names['sub_category'], $sub_category_data);
            //article
            $ability_title_css_selector = 'article#baobao.cl p.mdtitle';
            $ability_content_css_selector = 'div#baobaoState1';
            $health_title_css_selector = 'article#mama.cl p.mdtitle';
            $health_content_css_selector = 'article#mama.cl section';
            $nutrient_title_css_selector = 'article#baba.cl p.mdtitle';
            $nutrient_content_css_selector = 'article#baba.cl section';
            $shopping_title_css_selector = 'article#shopping.cl p.mdtitle';
            $shopping_content_css_selector = 'article#shopping.cl';
            $game_title_css_selector = 'div.mdwrap article#photo.cl p.mdtitle';
            $game_content_css_selector = 'div.mdwrap article#photo.cl section';
            $ability_title = $this->adapter_goutte->getText($ability_title_css_selector);
            $ability_content = $this->adapter_goutte->getHtml($ability_content_css_selector);
            $health_title = $this->adapter_goutte->getText($health_title_css_selector);
            $health_content = $this->adapter_goutte->getHtml($health_content_css_selector);
            $nutrient_title = $this->adapter_goutte->getText($nutrient_title_css_selector);
            $nutrient_content = $this->adapter_goutte->getHtml($nutrient_content_css_selector);
            $shopping_title = $this->adapter_goutte->getText($shopping_title_css_selector);
            $shopping_content = $this->adapter_goutte->getHtml($shopping_content_css_selector);
            $game_title = $this->adapter_goutte->getText($game_title_css_selector);
            $game_content = $this->adapter_goutte->getHtml($game_content_css_selector);

            $article_data = [
                'ma_sub_category' => $sub_category[0],
                'ma_main_category' => $main_category[0],
                'ma_title' => '',
                'ma_content' => '',
                'ma_url' => $url,
                'ma_status' => 1
            ];
            //ability
            $article_data['ma_title'] = $ability_title[0];
            $article_data['ma_content'] = $ability_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //health
            $article_data['ma_title'] = $health_title[0];
            $article_data['ma_content'] = $health_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //nutrient
            $article_data['ma_title'] = $nutrient_title[0];
            $article_data['ma_content'] = $nutrient_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //shopping
            $article_data['ma_title'] = $shopping_title[0];
            $article_data['ma_content'] = $shopping_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //game
            $article_data['ma_title'] = $game_title[0];
            $article_data['ma_content'] = $game_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
        }
    }

    private function _crawlBornRemainMonthsTips()
    {
        for ($i = 2; $i <= 12; $i++)
        {
            $url = str_replace('{which_month}', $i, $this->crawl_urls['born_remain_month']);
            $this->adapter_goutte->sendRequest($url);
            //main category
            $main_category_css_selector = 'div#headWeekNav.wrap.headWeekNav p em a.cur';
            $main_category = $this->adapter_goutte->getText($main_category_css_selector);
            if ($this->adapter_db->selectCount(
                    $this->table_names['main_category'],
                    ['mmc_name[=]' => $main_category[0]]
                ) == 0)
            {
                $main_category_data = [
                    'mmc_name' => $main_category[0]
                ];
                $this->adapter_db->insert($this->table_names['main_category'], $main_category_data);
            }
            //sub category
            $sub_category_css_selector = 'div.headWeekBox h1';
            $sub_category = $this->adapter_goutte->getText($sub_category_css_selector);
            $sub_category_data = [
                'msc_name' => $sub_category[0],
                'msc_main_category' => $main_category[0],
                'msc_url' => $url
            ];
            $this->adapter_db->insert($this->table_names['sub_category'], $sub_category_data);
            //article
            $ability_title_css_selector = 'article#baobao.cl p.mdtitle';
            $ability_content_css_selector = 'div#baobaoState1';
            $health_title_css_selector = 'article#mama.cl p.mdtitle';
            $health_content_css_selector = 'article#mama.cl section';
            $nutrient_title_css_selector = 'article#baba.cl p.mdtitle';
            $nutrient_content_css_selector = 'article#baba.cl section';
            $shopping_title_css_selector = 'article#shopping.cl p.mdtitle';
            $shopping_content_css_selector = 'article#shopping.cl';
            $game_title_css_selector = 'div.mdwrap article#photo.cl p.mdtitle';
            $game_content_css_selector = 'div.mdwrap article#photo.cl section';
            $ability_title = $this->adapter_goutte->getText($ability_title_css_selector);
            $ability_content = $this->adapter_goutte->getHtml($ability_content_css_selector);
            $health_title = $this->adapter_goutte->getText($health_title_css_selector);
            $health_content = $this->adapter_goutte->getHtml($health_content_css_selector);
            $nutrient_title = $this->adapter_goutte->getText($nutrient_title_css_selector);
            $nutrient_content = $this->adapter_goutte->getHtml($nutrient_content_css_selector);
            $shopping_title = $this->adapter_goutte->getText($shopping_title_css_selector);
            $shopping_content = $this->adapter_goutte->getHtml($shopping_content_css_selector);
            $game_title = $this->adapter_goutte->getText($game_title_css_selector);
            $game_content = $this->adapter_goutte->getHtml($game_content_css_selector);

            $article_data = [
                'ma_sub_category' => $sub_category[0],
                'ma_main_category' => $main_category[0],
                'ma_title' => '',
                'ma_content' => '',
                'ma_url' => $url,
                'ma_status' => 1
            ];
            //ability
            $article_data['ma_title'] = $ability_title[0];
            $article_data['ma_content'] = $ability_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //health
            $article_data['ma_title'] = $health_title[0];
            $article_data['ma_content'] = $health_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //nutrient
            $article_data['ma_title'] = $nutrient_title[0];
            $article_data['ma_content'] = $nutrient_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //shopping
            $article_data['ma_title'] = $shopping_title[0];
            $article_data['ma_content'] = $shopping_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
            //game
            $article_data['ma_title'] = $game_title[0];
            $article_data['ma_content'] = $game_content[0];
            $this->adapter_db->insert($this->table_names['article'], $article_data);
        }
    }

    private function _crawlBornYearMonthsTips()
    {
        for ($i = 1; $i <= 2; $i++)
        {
            if ($i == 1)
            {
                $title_css_selector = 'article.subWrap.cl section.main div.scBox.bdMain.bdMainYing h2';
                $content_css_selector = 'article.subWrap.cl section.main div.scBox.bdMain.bdMainYing p';
                $sub_category_css_selector = 'article.cBox.fS.cl div.imgTab.bdTop.bdTopYing.cl h3.weekly';
            }
            else
            {
                $title_css_selector = 'article.subWrap.cl section.main div.scBox.bdMain.bdMainYing.bdMainYou h2';
                $content_css_selector = 'article.subWrap.cl section.main div.scBox.bdMain.bdMainYing.bdMainYou p';
                $sub_category_css_selector = 'article.cBox.fS.cl div.imgTab.bdTop.bdTopYou.cl h3.weekly';
            }

            for ($j = 1; $j <= 12; $j++)
            {
                $url = str_replace(array('{which_year}', '{which_month}'), array($i, $j), $this->crawl_urls['born_year_month']);
                $this->adapter_goutte->sendRequest($url);
                //main category
                $main_category_css_selector = 'article.subWrap.cl aside.genSide.subSide div.scBox dl.bdSide dt.cl h3.l';
                $main_category = $this->adapter_goutte->getText($main_category_css_selector);
                $main_category_name = $main_category[count($main_category) - 1];
                if ($this->adapter_db->selectCount(
                        $this->table_names['main_category'],
                        ['mmc_name[=]' => $main_category_name]
                    ) == 0)
                {
                    $main_category_data = [
                        'mmc_name' => $main_category[0]
                    ];
                    $this->adapter_db->insert($this->table_names['main_category'], $main_category_data);
                }
                //sub category
                $sub_category = $this->adapter_goutte->getText($sub_category_css_selector);
                $sub_category_data = [
                    'msc_name' => $sub_category[0],
                    'msc_main_category' => $main_category[0],
                    'msc_url' => $url
                ];
                $this->adapter_db->insert($this->table_names['sub_category'], $sub_category_data);
                //article
                $titles = $this->adapter_goutte->getHtml($title_css_selector);
                $contents = $this->adapter_goutte->getHtml($content_css_selector);
                $article_data = [
                    'ma_sub_category' => $sub_category[0],
                    'ma_main_category' => $main_category_name,
                    'ma_title' => '',
                    'ma_content' => '',
                    'ma_url' => $url,
                    'ma_status' => 1
                ];

                foreach ($titles as $key => $value)
                {
                    $article_data['ma_title'] = trim(str_replace('\r\n', '', substr($value, 1)));
                    $insert_content = '';
                    if ($key == 0)
                    {
                        $article_data['ma_content'] = $contents[1] . '<br />' . $contents[2];
                    }
                    else if($key == count($titles) - 1)
                    {
                        $article_data['ma_content'] = $contents[count($contents) - 1];
                    }
                    else
                    {
                        $article_data['ma_content'] = $contents[$key + 2];
                    }

                    $this->adapter_db->insert($this->table_names['article'], $article_data);
                }
            }
        }
    }
}

$test = new Mama();
$test->run();