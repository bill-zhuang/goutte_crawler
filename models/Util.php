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
} 