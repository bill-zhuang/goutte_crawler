<?php
require_once 'Crawl_Base.php';

class LiaoxuefengPythonTutorial extends Crawl_Base
{
    private $_url_prefix;

    public function __construct()
    {
        parent::__construct();
        $this->crawl_urls = [
            0 => 'http://www.liaoxuefeng.com/wiki/0014316089557264a6b348958f449949df42a6d3a2e542c000',
        ];
        $this->table_names = [];
        $this->_url_prefix = 'http://www.liaoxuefeng.com';
        $this->_save_dir = 'D:/tutorial/liaoxuefeng-python-tutorial/';
    }

    public function run()
    {
        $this->createDirectory($this->_save_dir);
        $content_list = $this->_getPythonTutorialContent();
        $this->_savePythonChapterContent($content_list);
    }

    private function _getPythonTutorialContent()
    {
        $content_css_filter = 'div#main div.uk-container.x-container div.uk-grid div.uk-width-1-1 div.x-sidebar-left div.x-sidebar-left-content ul.uk-nav.uk-nav-side li a';
        $this->adapter_goutte->sendRequest($this->crawl_urls[0]);
        $href_url = $this->adapter_goutte->getHrefAttr($content_css_filter);
        $href_content = $this->adapter_goutte->getText($content_css_filter);
        $data = [];
        foreach ($href_url as $key => $url_postfix)
        {
            $data[$url_postfix] = $href_content[$key];
        }
        return $data;
    }

    private function _savePythonChapterContent(array $content_list)
    {
        $start = 1;
        $chapter_css_selector = 'div#main div.uk-container.x-container div.uk-grid div.uk-width-1-1 div.x-center div.x-content div.x-wiki-content';
        foreach ($content_list as $url_postfix => $content_name)
        {
            $this->adapter_goutte->sendRequest($this->_url_prefix . $url_postfix);
            $chapter_html = $this->_convertUTF82GB2312($this->adapter_goutte->getHtml($chapter_css_selector)[0]);
            $save_name = $this->_convertUTF82GB2312(
                $this->_removeInvalidCharOfFileName($start . '. ' . $content_name . '.html')
            );
            file_put_contents($this->_save_dir . $save_name, $chapter_html);
            $start++;
        }
    }

    private function _convertUTF82GB2312($content)
    {
        //remove filename invalid char
        return iconv('utf-8', 'gb2312//IGNORE', $content);
    }

    private function _removeInvalidCharOfFileName($filename, $replace = ' ')
    {
        return str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|'), $replace, $filename);
    }
}

$test = new LiaoxuefengPythonTutorial();
$test->run();