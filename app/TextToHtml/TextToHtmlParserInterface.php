<?php

namespace App\TextToHtml;

interface TextToHtmlParserInterface
{
    public function textToHtml(string $text): string;
}
