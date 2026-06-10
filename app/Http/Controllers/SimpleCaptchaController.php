<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class SimpleCaptchaController extends Controller
{
    const SIZE_X = 120;
    const SIZE_Y = 40;

    public function generate()
    {
        $code = rand(1000, 9999);
        session()->put('captcha', $code);
        session()->save();

        $code_chars = str_split(strval($code), 1);

        $image = imagecreatetruecolor(self::SIZE_X, self::SIZE_Y);

        $bgColor = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, self::SIZE_X, self::SIZE_Y, $bgColor);

        //show numbers
        foreach ($code_chars as $i => $code_char) {
            $textColor = imagecolorallocate($image, rand(120, 255), rand(120, 255), rand(120, 255));
            $font_size = rand(12, 18);
            $angle = (rand(0, 8) - 4) * 5;
            imagettftext($image, $font_size, $angle, 15 + $i * 25, 25, $textColor, '../public/font/Xolonium.ttf', $code_char);
        }

        //lines
        foreach (range(0, 8) as $n) {
            $line_color = imagecolorallocatealpha($image, rand(120, 255), rand(120, 255), rand(120, 255), 50);
            imageline($image, $n * 15, 0, $n * 15 + 10, self::SIZE_Y, $line_color);
        }

        //noise
        foreach (range(0, 150) as $n) {
            $pixel_color = imagecolorallocatealpha($image, rand(120, 255), rand(120, 255), rand(120, 255), 50);
            imagesetpixel($image, rand(0, self::SIZE_X), rand(0, self::SIZE_Y), $pixel_color);
        }

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}
