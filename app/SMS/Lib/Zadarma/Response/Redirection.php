<?php

namespace App\SMS\Lib\Zadarma\Response;


class Redirection extends Response
{
    public $sip_id;
    public $status;
    public $condition;
    public $destination;
    public $destination_value;
}
