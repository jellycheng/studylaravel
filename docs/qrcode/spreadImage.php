<?php

class spreadImage{

    /**
     * 吐出图片
     * @param $nickname 昵称
     * @param $mobile 手机号
     * @param $avatar 头像
     */
    public function get($nickname, $mobile, $avatar, $ticket, $templateFile, $outputFile) {
       // ob_end_clean();
        //header("Content-Type: image/jpeg");
        //$text = $nickname . '('. $mobile . ')';
        $text = $nickname;
        $target = $this->compose($text, $avatar, $ticket, $templateFile);
        imagejpeg($target, $outputFile, 90);
        imagedestroy($target);
        if(! file_exists($outputFile)){
            throw new Exception('生成本地二维码文件失败===>'.$outputFile);
        }
    }

    // 合成图片
    public function compose($text, $avatar, $ticket, $templateFile) {
        //$path = str_replace('/h5php/', '/public/static_partner/img/qrcode/', PROJECT_PATH);
        $path = './img/';
        //$file = $this->type == TYPE_HHR ? 'temple_hhr.jpg' : 'temple_fn.jpg';
        $sourceFile = $path . $templateFile;
        if(! file_exists($sourceFile)){
            throw new Exception('模版文件不存在===>'.$sourceFile);
        }

        $font = $path . 'st.ttf';
        if(! file_exists($font)){
            throw new Exception('字体文件不存在===>'.$font);
        }
		/**
        Partner_Lib_Log::writeFile(array(
            'url' => '',
            'requestMethod' => '...',
            'requestParam' => '模板文件：' . $sourceFile,
            'expendTime' => '0',
            'responseData' => '调试',
            'path' => Partner_Lib_Util::getTouchLogPath('hhrdebuglog'),
        ));
		*/
        $main = imagecreatefromjpeg($sourceFile);
        $width = imagesx($main);
        $height = imagesy($main);
        $target = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $white);

        imagecopyresampled($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);

        $fontSize = 20;//像素字体
        $fontColor = imagecolorallocate($target, 255, 0, 0);//字的RGB颜色
        imagettftext($target, $fontSize, 0, 240, 70, $fontColor, $font, $text);

        // 失效时间
        $endTime = date('Y-m-d H:i', time()+7*24*3600);
        $endTimeFontColor = imagecolorallocate($target, 50, 50, 50);//字的RGB颜色
        imagettftext($target, 24, 0, 240, 752, $endTimeFontColor, $font, $endTime);

        //imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用

        //imagefilledpolygon($target, array (10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor);//画三角形
        //imageline($target, 100, 200, 20, 142, $fontColor);//画线
        //imagefilledrectangle($target, 50, 100, 250, 150, $fontColor);//画矩形

        //放头像上去
        $child1 = $this->cut($avatar, 100);
        imagecopymerge($target, $child1, 42, 45, 0, 0, imagesx($child1), imagesy($child1), 100);

        //放二维码上去
        $child2 = $this->cut($ticket, 320);
        imagecopymerge($target, $child2, 140, 240, 0, 0, imagesx($child2), imagesy($child2), 100);

        imagedestroy($main);
        imagedestroy($child1);
        imagedestroy($child2);

        return $target;
    }


    // 图片缩放
    public function cut($path, $size){
        /* Partner_Lib_Log::writeFile(array(
            'url' => '',
            'requestMethod' => '...',
            'requestParam' => '头像文件：' . $path,
            'expendTime' => '0',
            'responseData' => '调试',
            'path' => Partner_Lib_Util::getTouchLogPath('hhrdebuglog'),
        )); */
        $src = imagecreatefromjpeg($path);
        // 取得源图片的宽度和高度
        list($width, $high) = getimagesize($path);
        // 声明一个$px宽，$px高的真彩图片资源
        $target = imagecreatetruecolor($size, $size);
        // 图片缩放
        imagecopyresampled($target, $src, 0, 0, 0, 0, $size, $size, $width, $high);
        return $target;
    }

}