<?php


namespace App\PaymentsAPI;


class PiastrixClientException extends \Exception {
    public function __construct($message, $error_code, \Exception $previous = null) {
        $this->error_code = $error_code;
        parent::__construct($message, $error_code, $previous);
    }
}
