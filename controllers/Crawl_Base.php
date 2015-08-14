<?php

require_once '../models/Goutte_Crawl.php';
require_once '../models/DBTableFactory.php';

class Crawl_Base
{
    /**
     * @var Goutte_Crawl
     */
    public $adapter_goutte;
    /**
     * @var DBTableFactory
     */
    public $adapter_db;
    public $crawl_urls;
    public $table_names;
    public $url_prefix;

    public function __construct()
    {
        set_time_limit(0);
        $this->adapter_goutte = new Goutte_Crawl();
        $this->adapter_db = new DBTableFactory();
        $this->crawl_urls = [];
        $this->table_names = [];
        $this->url_prefix = '';
    }

    /*
     * run method should override
     * */
    public function run()
    {

    }

    public function createDirectory($dir)
    {
        if ($dir !== '' && !is_dir($dir))
        {
            mkdir($dir, '0777', true);
        }
    }
} 