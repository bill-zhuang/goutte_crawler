<?php

class Download
{
    public function sendRequest($url, array $postData, $method = 'GET')
    {
        $postData = http_build_query($postData);

        $options = [
            'http' => [
                'method' => $method,
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 15 * 60,
            ],
        ];

        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

    public function normalDownload($url, $fileName, $dir = null)
    {
        $fullPath = ($dir == null) ? $fileName : $dir . $fileName;

        $file_content = file_get_contents($url);
        file_put_contents($fullPath, $file_content);
    }

    public function curlSingleDownload($url, $fileName, $dir = null)
    {
        if($dir != null)
        {
            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }
        }

        $fullPath = ($dir == null) ? $fileName : $dir . $fileName;

        if (file_exists($fullPath))
        {
            return;
            //unlink($fullPath);
        }

        $fp = fopen($fullPath, 'w+');

        if ($fp)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_exec($ch);
            curl_close($ch);
        }
        else
        {
            echo "Download {$fileName} file from {$url} failed.<br>";
            return;
        }

        fclose($fp);
    }

    /**
    * Download file through multi-thread by curl.
    * @param array $download_urls
    * <p>key-filename, value-url.</p>
    * @param string $dir
    * <p>directory to save the files.</p>
    * @param integer $download_number [optinal]
    * <p>download files at one time, default 100.</p>*/
    public function curlMultipleDownloadToDisk(array $download_urls, $dir, $download_number = 100)
    {
        if(!file_exists($dir))
        {
            mkdir($dir, 0777, true);
        }

        $mh=curl_multi_init();
        $download_chunks = array_chunk($download_urls, $download_number, true);

        foreach($download_chunks as $download_chunk)
        {
            foreach($download_chunk as $filename => $url)
            {
                if (!file_exists($dir . $filename))
                {
                    $conn[$filename] = curl_init($url);
                    $fp[$filename] = fopen($dir . $filename, "w+");

                    curl_setopt($conn[$filename], CURLOPT_FILE, $fp[$filename]);
                    curl_setopt($conn[$filename], CURLOPT_HEADER, 0);
                    curl_setopt($conn[$filename], CURLOPT_CONNECTTIMEOUT, 60);
                    curl_multi_add_handle($mh, $conn[$filename]);
                }
            }

            if (!empty($conn))
            {
                do
                {
                    $n = curl_multi_exec($mh, $active);
                }while($active);

                foreach($download_chunk as $filename => $url)
                {
                    curl_multi_remove_handle($mh, $conn[$filename]);
                    curl_close($conn[$filename]);
                    fclose($fp[$filename]);
                }
            }
        }

        curl_multi_close($mh);
    }

    public function getCurlMultipleDownloadContent(array $download_urls, $download_number = 100)
    {
        $doc_data = [];
        $mh=curl_multi_init();
        $download_chunks = array_chunk($download_urls, $download_number, true);

        foreach($download_chunks as $download_chunk)
        {
            foreach($download_chunk as $key => $url)
            {
                $conn[$key] = curl_init($url);

                curl_setopt($conn[$key], CURLOPT_HEADER, 0);
                curl_setopt($conn[$key], CURLOPT_CONNECTTIMEOUT, 60);
                curl_setopt($conn[$key], CURLOPT_RETURNTRANSFER, 1);
                curl_multi_add_handle($mh, $conn[$key]);
            }

            do
            {
                $n = curl_multi_exec($mh, $active);
            }while($active);

            foreach($download_chunk as $key => $url)
            {
                $doc_data[$key] = curl_multi_getcontent($conn[$key]);

                curl_multi_remove_handle($mh, $conn[$key]);
                curl_close($conn[$key]);
            }
        }

        curl_multi_close($mh);

        return $doc_data;
    }
}