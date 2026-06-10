<?php

namespace App\Helpers;

class QrCodeGen
{
    /**
     * generate qr image data
     * @example <img src="{{QrCodeGen::genImage('test qr image text')}}">
     * @param string $text
     * @param string $ecc L, M, Q, H (QR_ECCLeve
     * @param int $pixel_size
     * @param int $frame_size
     * @return string
     */
    public static function genImage(string $text, $ecc = 'L', $pixel_size = 10, $frame_size = 3)
    {
        include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'/phpqrcode/qrlib.php');

        $file = tmpfile();
        $path = stream_get_meta_data($file)['uri'];

        \QRcode::png($text, $path, $ecc, $pixel_size, $frame_size);

        $base64 = base64_encode(file_get_contents($path));
        $image_data = 'data:' . mime_content_type($path) . ';base64,' . $base64;
        fclose($file);
        return $image_data;
    }
}
