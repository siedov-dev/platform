<?php

namespace App\SMS\Lib\Zadarma\Response;


class SpeechRecognition extends Response
{
    public $lang;
    public $recognitionStatus;
    public $otherLangs;
    public $phrases;
    public $words;
}
