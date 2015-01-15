<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-13
 * Time: 上午11:32
 */

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

    public function __construct()
    {
        set_time_limit(0);
        $this->adapter_goutte = new Goutte_Crawl();
        $this->adapter_db = new DBTableFactory();
        $this->crawl_urls = [];
        $this->table_names = [];
    }

    /*
     * run method should override
     * */
    public function run()
    {

    }
} 