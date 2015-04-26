<?php

class Zxxk_captcha
{
    private $_threshold;
    private $_image_path;

    public function __construct($path)
    {
        //captcha url: http://passport.zxxk.com/RanImg.aspx
        $this->_threshold = 80;
        $this->_image_path = $path;
    }

    private function _getFilteredRedChannel()
    {
        $captcha_handle = imagecreatefromgif($this->_image_path);
        $image_size = getimagesize($this->_image_path);
        $red_channel = [];
        for ($i = 0; $i < $image_size[1]; $i++)
        {
            for ($j = 0; $j < $image_size[0]; $j++)
            {
                $rgb = imagecolorat($captcha_handle, $j, $i);
                $rgb_array = imagecolorsforindex($captcha_handle, $rgb);
                if ($rgb_array['red'] > $this->_threshold)
                {
                    $red_channel[$i][$j] = 0;
                }
                else
                {
                    $red_channel[$i][$j] = 255;
                }
            }
        }

        for ($i = 1, $height = $image_size[1] - 1; $i < $height; $i++)
        {
            for ($j = 1, $width = $image_size[0] - 2; $j < $width; $j++)
            {
                if ($j % 21 == 0)
                {
                    echo '   ';
                }
                $pixel = $red_channel[$i][$j];
                echo ($pixel == 1 ? '<font color="red"><b>' . $pixel . '</b></font>' : $pixel) . ' ';
            }
            echo "\r\n";
        }
    }




} 