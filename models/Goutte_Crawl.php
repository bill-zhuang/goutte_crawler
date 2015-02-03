<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-3
 * Time: 上午11:10
 */
use Goutte\Client;
require_once '../library/goutte.phar';
require_once 'Util.php';
class Goutte_Crawl
{
    /**
     * @var Goutte\Client
     */
    private $_goutte_client;
    private $_goutte_crawler;
    private $_methods;

    public function __construct()
    {
        $this->_goutte_client = new Client();
        $this->_methods = array('GET', 'POST');
    }

    public function sendRequest($url, $method = 'GET')
    {
        $method = strtoupper($method);
        if (!in_array($method, $this->_methods))
        {
            $method = $this->_methods[0];
        }

        $this->_goutte_crawler = $this->_goutte_client->request($method, $url);
    }

    public function getRedirectUrl()
    {
        return $this->_goutte_client->getHistory()->current()->getUri();
    }

    public function getWholeHtmlPage()
    {
        $this->_isCrawlerInit();
        return $this->_goutte_crawler->html();
    }

    public function getHtml($css_selector)
    {
        $this->_isCrawlerInit();
        return $this->_goutte_crawler->filter($css_selector)->each(function ($node) {
            return trim($node->html());
        });
    }

    public function getText($css_selector)
    {
        $this->_isCrawlerInit();
        return $this->_goutte_crawler->filter($css_selector)->each(function ($node) {
            return trim($node->text());
        });
    }

    public function getTableTDText($css_selector, $td_num)
    {
        $this->_isCrawlerInit();
        return $this->_goutte_crawler->filter($css_selector)->each(function ($node) use($td_num) {
            return trim($node->filter('td')->eq($td_num)->text());
        });
    }

    public function getHrefAttr($css_selector)
    {
        $this->_isCrawlerInit();
        return $this->_goutte_crawler->filter($css_selector)->each(function ($node) {
            $href = $node->extract(array('href'));
            return isset($href[0]) ? $href[0] : '';
        });
    }

    public function getImageSrcAttr($css_selector)
    {
        $this->_isCrawlerInit();
        return $this->_goutte_crawler->filter($css_selector)->each(function ($node) {
            $src = $node->extract(array('src'));
            return isset($src[0]) ? $src[0] : '';
        });
    }

    public function getAttrByName($css_selector, $attr_name)
    {
        $this->_isCrawlerInit();
        return $this->_goutte_crawler->filter($css_selector)->each(function ($node) use($attr_name){
            $attr_value = $node->extract(array($attr_name));
            return isset($attr_value[0]) ? $attr_value[0] : '';
        });
    }

    public function setTimeOut($seconds = 60)
    {
        $seconds = intval($seconds);
        if ($seconds <= 0)
        {
            $seconds = 60;
        }

        $this->_goutte_client->getClient()->setDefaultOption('config/curl/' . CURLOPT_TIMEOUT, $seconds);
    }

    public function setFakeHeaderIP()
    {
        $fake_ip = Util::generateFakeIP();
        $this->_goutte_client->getClient()->setDefaultOption('headers/CLIENT-IP', $fake_ip);
        $this->_goutte_client->getClient()->setDefaultOption('headers/X-FORWARDED-FOR', $fake_ip);
    }

    public function getCookie($name, $path = '/', $domain = null)
    {
        return $this->_goutte_client->getCookieJar()->get($name, $path, $domain);
    }

    public function setCookie($cookie)
    {
        $this->_goutte_client->getCookieJar()->set($cookie);
    }

    private function _isCrawlerInit()
    {
        if ($this->_goutte_crawler === null)
        {
            echo 'Init request url';
            exit;
        }
        else
        {
            return true;
        }
    }
} 