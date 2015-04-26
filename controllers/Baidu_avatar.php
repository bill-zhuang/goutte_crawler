<?php

$host = '127.0.0.1';
$db_name = 'crawl';
$username = 'root';
$password = '123456';
$option = [];
$pdo = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password, $option);
//downloadAvatar($pdo);exit;
$avatar_urls = [
    'http://image.baidu.com/i?tn=resultjsonavatarnew&ie=utf-8&word=%E5%A4%B4%E5%83%8F&cg=head&pn={pn}&rn=60&itg=1&z=0&fr=&width=&height=&lm=7&ic=0&s=0&st=-1',
    'http://image.baidu.com/i?tn=resultjson_com&ipn=rj&ct=201326592&is=&fp=result&queryWord=%E5%8A%A8%E7%89%A9&cl=2&lm=-1&ie=utf-8&oe=utf-8&adpicid=&st=-1&z=&ic=0&word=%E5%8A%A8%E7%89%A9&s=3&se=1&tab=&width=&height=&face=&istype=2&qc=&nc=1&fr=%26fr%3D&pn={pn}&rn=60&1429523335088=',
    'http://image.baidu.com/i?tn=resultjson_com&ipn=rj&ct=201326592&is=&fp=result&queryWord=QQ%E6%B5%B7%E9%87%8F%E5%A4%B4%E5%83%8F&cl=2&lm=-1&ie=utf-8&oe=utf-8&adpicid=&st=-1&z=&ic=0&word=QQ%E6%B5%B7%E9%87%8F%E5%A4%B4%E5%83%8F&s=&se=1&tab=&width=&height=&face=0&istype=2&qc=&nc=1&fr=%26fr%3D&pn={pn}&rn=60&1429756418455=',
];
$fetch_rules = [
    ['fetch_field' => 'imgs', 'img_field' => 'objURL'],
    ['fetch_field' => 'data', 'img_field' => 'thumbURL'],
    ['fetch_field' => 'data', 'img_field' => 'thumbURL'],
];
foreach ($avatar_urls as $key => $avatar_url)
{
    $fetch_filed = $fetch_rules[$key]['fetch_field'];
    $img_field = $fetch_rules[$key]['img_field'];
    for ($i = 0; ; $i += 60)
    {
        $url = str_replace('{pn}', $i, $avatar_url);
        $content = getBaiduResponse($url);
        $decode_data = json_decode($content, true);
        if (isset($decode_data[$fetch_filed]) && count($decode_data[$fetch_filed]) > 0)
        {
            if (empty($decode_data[$fetch_filed][0]))
            {
                break;
            }

            $avatars = [];
            $img_url = '';
            foreach ($decode_data[$fetch_filed] as $per_avatar)
            {
                if (!empty($per_avatar))
                {
                    $avatars[] = $per_avatar[$img_field];
                }
            }
            insertAvatar($pdo, $avatars);
        }
        else
        {
            break;
        }
    }
}

function getBaiduResponse($url)
{
    $ch = curl_init($url);
    $agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0';
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    return curl_exec($ch);
}

function insertAvatar(PDO $pdo, array $avatars)
{
    $data_keys = ['ba_imgurl', 'ba_status', 'ba_create_time', 'ba_update_time'];
    $insert_sql_prefix = 'INSERT INTO baidu_avatar(' . implode(',', $data_keys);
    foreach ($avatars as $avatar)
    {
        $data_values = [$avatar, 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')];
        $insert_sql = $insert_sql_prefix . ') VALUES ("' . implode('", "', $data_values) . '")';
        $pdo->exec($insert_sql);
    }
}

function downloadAvatar(PDO $pdo)
{
    $save_dir = 'E:\baidu_avatar\\';
    $offset = 0;
    $limit = 100;
    while(1)
    {
        $sql = 'SELECT ba_id, ba_imgurl FROM baidu_avatar LIMIT ' . $offset . ', ' . $limit;
        $data = $pdo->query($sql)->fetchAll();
        if (count($data) > 0)
        {
            $items = [];
            foreach ($data as $value)
            {
                $extension = pathinfo($value['ba_imgurl'], PATHINFO_EXTENSION);
                if (strlen($extension) <= 4)
                {
                    $save_name = $value['ba_id'] . '.' . $extension;
                    $items[$save_name] = $value['ba_imgurl'];
                }
            }
            curlMultipleDownload($items, $save_dir, 10);
            $offset += $limit;
        }
        else
        {
            break;
        }
    }
}

function curlMultipleDownload(array $filenameUrl, $dir, $downloadNum = 100)
{
    if(!file_exists($dir))
    {
        mkdir($dir, 0777, true);
    }

    $mh=curl_multi_init();
    $split_urls = array_chunk($filenameUrl, $downloadNum, true);

    $agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0';
    $referer = 'http://image.baidu.com';
    foreach($split_urls as $item)
    {
        $curl_handles = [];
        $file_handles = [];
        foreach($item as $filename => $url)
        {
            if(!is_file($dir . $filename))
            {
                $temp_handle = fopen($dir . $filename, 'w+');
                if ($temp_handle !== false)
                {
                    $curl_handles[$filename] = curl_init($url);
                    $file_handles[$filename] = $temp_handle;

                    curl_setopt($curl_handles[$filename], CURLOPT_FILE, $file_handles[$filename]);
                    curl_setopt($curl_handles[$filename], CURLOPT_HEADER, 0);
                    curl_setopt($curl_handles[$filename], CURLOPT_USERAGENT, $agent);
                    //important, only for baidu image, first $avatar_urls should comment following line
                    curl_setopt($curl_handles[$filename], CURLOPT_REFERER, $referer);
                    curl_setopt($curl_handles[$filename], CURLOPT_CONNECTTIMEOUT, 60);
                    curl_multi_add_handle($mh, $curl_handles[$filename]);
                }
            }
        }

        do
        {
            $n = curl_multi_exec($mh, $active);
        }while($active);

        foreach($item as $filename => $url)
        {
            if (isset($file_handles[$filename]))
            {
                curl_multi_remove_handle($mh, $curl_handles[$filename]);
                curl_close($curl_handles[$filename]);
                fclose($file_handles[$filename]);
            }
        }
    }

    curl_multi_close($mh);
}