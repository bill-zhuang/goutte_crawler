<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-3
 * Time: 上午11:39
 */

class Util
{
    public static function generateFakeIP()
    {
        $ip_fields = array();
        for ($i = 0; $i < 4; $i++)
        {
            $ip_fields[] = rand(1, 254);
        }

        return implode('.', $ip_fields);
    }

    public static function getAccount($forum_name)
    {
        $account_ini_path = '../config/account.ini';
        if (file_exists($account_ini_path))
        {
            $account_config = parse_ini_file('../config/account.ini', true);
            if (!empty($account_config[$forum_name]))
            {
                return $account_config[$forum_name];
            }
        }

        return null;
    }
} 