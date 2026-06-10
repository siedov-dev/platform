<?php

namespace App\Helpers;

class Captcha
{
    public static function verify() : bool
    {
        if (!isset($_POST['g-recaptcha-response'])) {
            return false;
        }

        // For IP transparency using cloudflare.
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        $post_data = http_build_query([
            'secret'   => config('captcha.secret'),
            'response' => $_POST['g-recaptcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]);
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            ]
        ];
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        if (!$result->success) {
            return false;
        }
        return true;
    }
}
