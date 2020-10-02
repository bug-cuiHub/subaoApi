<?php
namespace app\index\util;

use think\Session;

class VerifyCode {
    //创建验证码
    public function createVerifyCode() {
        header('content-type:image/png');
        $image_width = 100;
        $image_height = 20;
        $image = @imagecreatetruecolor($image_width, $image_height);
        $bgcolor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgcolor);
        $content = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
        $captcha = "";
        for ($i = 0; $i < 4; $i++) {
            $fontsize = 50;
            $fontcolor = imagecolorallocate($image, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
            $fontcontent = substr($content, mt_rand(0, strlen($content)), 1);
            //这个点用于将字符串拼接，没有错，嘻嘻
            $captcha .= $fontcontent;
            $x = ($i * 100 / 4) + mt_rand(5, 10);
            $y = mt_rand(1, 5);    
            imagestring($image, $fontsize, $x, $y, "$fontcontent", $fontcolor);
        }

        Session::set('captcha',strtolower($captcha));
        // for ($i = 0; $i < 200; $i++) {
        //     $pointcolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
        //     imagesetpixel($image, mt_rand(1, 99), mt_rand(1, 29), $pointcolor);
        // }
        // for ($i = 0; $i < 3; $i++) {
        //     $linecolor = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
        //     imageline($image, mt_rand(1, 99), mt_rand(1, 29), mt_rand(1, 99), mt_rand(1, 29), $linecolor);
        // }
        //开启缓冲区
        ob_start();
        //将图片输入到缓冲区
        imagepng($image);
        //获取缓冲区内容
        $content = ob_get_clean();
        //释放图片资源
        imagedestroy($image);
        //将缓冲区输入到响应 并设置响应头
        return response($content, 200)->contentType("image/png");
    }
}