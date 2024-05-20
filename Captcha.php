<?php
//图片验证码类
namespace mkphp\captcha;
class Captcha
{
    private $im;//图像资源
    private $width = 100;//图片宽度
    private $height = 34;//图片高度
    private $pointNum = 10;//在图像上所画点的个数
    private $lineNum = 1;//在图像上所画线的条数
    private $fontNum = 4;//验证字符个数

    public function __construct()
    {
    }

    //设定图像大小
    public function setSize($h = 34)
    {
        $this->width = $this->fontNum * 25;// 根据字符计算图像宽度
        $this->height = $h;
    }

    //建立图像
    public function createPic()
    {
        $this->im = imagecreate($this->width, $this->height);
        imagecolorallocate($this->im, 200, 200, 200);
    }

    //设置干扰点
    public function setPoint()
    {

        for ($i = 0; $i < $this->pointNum; $i++) {
            $color = imagecolorallocate($this->im, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->im, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    //设置干扰线条
    public function setLine()
    {
        if (empty($this->lineNum)) {
            $this->lineNum = 1;
        }
        for ($i = 0; $i < $this->lineNum; $i++) {
            $line_color = imagecolorallocate($this->im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));//生成干扰线条颜色
            imageline($this->im, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $line_color);
        }
    }

    //生成随机字符
    public function randStr()
    {
        $len = 4;
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYabcdghjkmnprstuvwxy23456789';
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);

        return $str;
    }

    public static function check($str){
        $s_str = session()->get('captcha');
        session()->del('captcha');
        if ($s_str === strtolower(trim($str))) {
             return  true;
        }
        return false;
    }

    //写入验证字符
    public function show()
    {
        $this->setSize();//设置图像高度
        $this->createPic();//建立图像
        $string = $this->randStr();//得到随时字符
        session()->set('captcha', strtolower($string),300);

        $this->setPoint();//绘制干扰雪花
        for ($i = 0; $i < $this->fontNum; $i++) {
            $font_color = imagecolorallocate($this->im, mt_rand(100, 150), mt_rand(100, 150), mt_rand(100, 150));
            imagettftext($this->im, 16, mt_rand(-30, 30), $i * (mt_rand(20, 25)) + 8, ceil($this->height / 1.4), $font_color, __DIR__ . '/fonts/font.ttf', $string[$i]);

        }
        $this->setLine();//绘制干扰线
        ob_start();
        response()->setHeaders(['Pragma' => 'no-cache']);
        response()->contentType('image/png');
        imagepng($this->im);//输出图像
        imagedestroy($this->im);
        response()->end(ob_get_clean());
    }
}