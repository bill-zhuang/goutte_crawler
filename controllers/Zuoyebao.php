<?php

require_once 'Crawl_Base.php';
class Zuoyebao extends Crawl_Base
{
    private $_url_prefix;
    private $_is_crontab;

    public function __construct($is_crontab)
    {
        parent::__construct();
        $this->crawl_urls = [
            'http://www.zuoyebao.com/s?q='
        ];
        $this->table_names = [
            'grade' => 'crawl_grade',
            'course' => 'crawl_course',
            'exam' => 'crawl_zuoyebao'
        ];
        $this->_url_prefix = 'http://www.zuoyebao.com/s?';
        $this->_is_crontab = boolval($is_crontab);
    }

    public function run()
    {
        $categories = $this->_getCategories();
        $cartesian_products = $this->_getCartesianProduct($categories);
        foreach ($cartesian_products as $item)
        {
            $page_url = $this->_url_prefix . $item['filter'];
            $total_page = $this->_getTotalPage($page_url);
            for ($i = 1; $i <= $total_page ; $i++)
            {
                $this->adapter_goutte->setFakeHeaderIP();

                $crawl_exam_url = $page_url . '&p=' . $i;
                $this->_crawlExam($crawl_exam_url, $item['ccid'], $item['grade_name']);
            }
        }
    }

    private function _getCategories()
    {
        $category_css_selector = 'div.www-srh-filter div a';
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $names = $this->adapter_goutte->getText($category_css_selector);
        $urls = $this->adapter_goutte->getHrefAttr($category_css_selector);
        $categories = [];
        $order = 0;
        foreach ($names as $key => $name)
        {
            if ($name == '不限')
            {
                $order++;
                continue;
            }
            else
            {
                if ($order < 3 || ($order ==3 && $name == '选择题'))
                {
                    $categories[$order][$name] = str_replace(['/s?k=', '/s?'], '', $urls[$key]);
                }
            }
        }

        return $categories;
    }

    private function _getCartesianProduct(array $filter_conditions)
    {
        $cartesian_products = [];
        foreach ($filter_conditions[1] as $grade_name => $grade_filter)
        {
            foreach ($filter_conditions[2] as $course_name => $course_filter)
            {
                $ccid = ($course_name == '') ? 0 : $this->_getCcid($course_name);
                $cartesian_products[] = [
                    'filter' => 'k=' . $grade_filter . '+' . $course_filter . '&' . $filter_conditions[3]['选择题'],
                    'grade_name' => $grade_name,
                    'ccid' => $ccid,
                ];
            }
        }

        return $cartesian_products;
    }

    private function _getTotalPage($url)
    {
        $total_page = 1;

        $total_page_css_selector = 'div.g-pages a';
        $this->adapter_goutte->sendRequest($url);
        $pages = $this->adapter_goutte->getText($total_page_css_selector);
        if (count($pages) >= 2)
        {
            $total_page = intval(str_replace('.', '', $pages[count($pages) - 2]));
            $total_page = ($total_page > 0) ? $total_page : 1;
        }

        return $total_page;
    }

    private function _crawlExam($exam_url, $ccid, $grade_name)
    {
        $question_css_selector = 'ol.www-srh-list li.st div.q-body h1.q-tigan';
        $choice_css_selector = 'ol.www-srh-list li.st div.q-body ol.q-xz';
        $question_choice_css_selector = 'ol.www-srh-list li.st div.q-body';
        $tag_css_selector = 'ol.www-srh-list li.st div.aside span.tags';
        $question_url_css_selector = 'ol.www-srh-list li.st div.aside span.link a';

        $this->adapter_goutte->sendRequest($exam_url);
        $questions = $this->adapter_goutte->getHtml($question_css_selector);
        $choices = $this->adapter_goutte->getHtml($choice_css_selector);
        $tags = $this->adapter_goutte->getText($tag_css_selector);
        $question_urls = $this->adapter_goutte->getHrefAttr($question_url_css_selector);
        if (count($questions) != count($question_urls))
        {
            $question_choices = $this->adapter_goutte->getHtml($question_choice_css_selector);
            $preg_question = '/"q\-tigan">(.+?)<\/h1/us';
            $preg_choice = '/"q\-xz">(.+?)<\/ol/us';
            $questions = [];
            $choices = [];
            foreach ($question_choices as $key => $question_choice)
            {
                $is_question_match = preg_match_all($preg_question, $question_choice, $question_matches);
                $is_choice_match = preg_match_all($preg_choice, $question_choice, $choice_matches);
                if ($is_question_match && $is_choice_match)
                {
                    $questions[] = $question_matches[1][0]
                        . (isset($question_matches[1][1]) ? $question_matches[1][1] : '');
                    $choices[] = $choice_matches[1][0];
                }
                else
                {
                    unset($question_urls[$key]);
                    unset($tags[$key]);
                }
            }
        }

        if (count($questions) == count($choices) && count($questions) == count($question_urls))
        {
            $question_urls = array_values($question_urls);
            $tags = array_values($tags);
            foreach ($questions as $key => $question)
            {
                $grade_name = $this->_getGradeName($grade_name, $tags[$key]);
                $cgid = $this->_getCgid($grade_name);
                //answer may multiple
                $answers = $this->_getAnswers($question_urls[$key]);

                $question_data = [
                    'cz_name' => $this->_removeLatexAttribute($question),
                    'cz_choice' => json_encode($this->_splitChoices($choices[$key])),
                    'cz_answer' => $answers,
                    'cz_tag' => str_replace('关键字:', '', $tags[$key]),
                    'cz_url' => 'http://m.zuoyebao.com' . $question_urls[$key],
                    'cg_id' => $cgid,
                    'cc_id' => $ccid,
                    'cz_status' => 1,
                    'cz_create_time' => date('Y-m-d H:i:s'),
                    'cz_update_time' => date('Y-m-d H:i:s'),
                ];

                $this->adapter_db->insert($this->table_names['exam'], $question_data);
            }
        }
    }

    private function _getGradeName($grade, $tags)
    {
        if ($grade == '小学')
        {
            $preg = '/([一二三四五六]年级)/u';
            $default_grade = '六年级';
        }
        else if ($grade == '初中')
        {
            $preg = '/(初[一二三])/u';
            $default_grade = '初三';
        }
        else
        {
            $preg = '/(高[一二三])/u';
            $default_grade = '高三';
        }

        $is_match = preg_match($preg, $tags, $matches);
        if ($is_match)
        {
            return $matches[1];
        }
        else
        {
            return $default_grade;
        }
    }

    private function _splitChoices($choice_html)
    {
        $data = [];
        $preg_choice = '/sn">([^<]+)<\/span[^"]+"xx">(.+?)<\/div>/um';
        $is_match = preg_match_all($preg_choice, $choice_html, $matches);
        if ($is_match)
        {
            $preg_letter = '/[^a-z]/i';
            for ($i = 0, $len = count($matches[1]); $i < $len; $i++)
            {
                $select = strtoupper(preg_replace($preg_letter, '', $matches[1][$i]));
                $data[$select] = $this->_removeLatexAttribute($matches[2][$i]);
            }
        }
        
        return $data;
    }

    private function _getAnswers($query)
    {
        $url = 'http://m.17xueshe.com' . $query;
        $this->adapter_goutte->sendRequest($url);
        $answer_css_selector = 'div.q-view-sec.q-view-lt';
        $answer = $this->adapter_goutte->getText($answer_css_selector);
        if (!empty($answer))
        {
            $is_match = preg_match('/([a-z]+)/i', $answer[0], $matches);
            if ($is_match)
            {
                return strtoupper($matches[1]);
            }
        }

        return '';
    }

    private function _removeLatexAttribute($img_tag)
    {
        $latex_attr = 'class="latex"';
       return str_replace($latex_attr, '', $img_tag);
    }

    private function _getCcid($course_name)
    {
        $column = 'cc_id';
        $where = [
            'AND' => [
                'cc_name[=]' => $course_name,
                'cc_status[=]' => 1
            ],
        ];
        $ccid = $this->adapter_db->selectOne($this->table_names['course'], $column, $where);
        if (!$ccid)
        {
            $ccid = $this->_insertCourse($course_name);
        }

        return $ccid;
    }

    private function _insertCourse($course_name)
    {
        $insert_course_data = [
            'cc_name' => $course_name,
            'cc_status' => 1,
            'cc_create_time' => date('Y-m-d H:i:s'),
            'cc_update_time' => date('Y-m-d H:i:s')
        ];
        $ccid = $this->adapter_db->insert($this->table_names['course'], $insert_course_data);

        return $ccid;
    }

    private function _getCgid($grade_name)
    {
        $column = 'cg_id';
        $where = [
            'AND' => [
                'cg_name[=]' => $grade_name,
                'cg_status[=]' => 1
            ]
        ];
        $cgid = $this->adapter_db->selectOne($this->table_names['grade'], $column, $where);
        $cgid = intval($cgid);

        return $cgid;
    }

}

$test = new Zuoyebao(false);
$test->run();