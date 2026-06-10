<?php

namespace App;

/**
 * @deprecated
 */
class GameinvestEmail
{
    private $_text;
    private $_html;
    private $_from = 'Gameinvests <support@gameinvests.com>';
    private $_to;
    private $_subject;


    public function __construct($data)
    {
        $this->_text = isset($data['text']) ? $data['text'] : '';
        $this->_html = isset($data['html']) ? $data['html'] : '';
        $this->_subject = isset($data['subject']) ? $data['subject'] : '';
        $this->_to = isset($data['to']) ? $data['to'] : '';
    }


    public function send()
    {
        ini_set('sendmail_from', 'support@gameinvests.com');

        // Unique boundary
        $boundary = md5( uniqid() . microtime() );

        // Add From: header
        $headers = "From: " . $this->_from . "\r\n";
        $headers .= 'Reply-To: support@gameinvests.com' . "\r\n";
        $headers .= 'Return-Path: support@gameinvests.com' . "\r\n";

        // Specify MIME version 1.0
        $headers .= "MIME-Version: 1.0\r\n";

        // Tell e-mail client this e-mail contains alternate versions
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";


        // Plain text version of message
        $body = "--$boundary\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: base64\r\n\r\n";

        $body .= chunk_split( base64_encode(strip_tags($this->_text)));

        // HTML version of message
        $body .= "--$boundary\r\n" .
            "Content-Type: text/html; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: base64\r\n\r\n";


        $body .= chunk_split(base64_encode($this->_html));
        $body .= "--$boundary--";

        // Send Email
        return mail($this->_to, $this->_subject, $body, $headers, '-fsupport@gameinvests.com');
    }
}
