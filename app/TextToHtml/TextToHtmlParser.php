<?php

namespace App\TextToHtml;

use App\Helpers\Localization;
use App\UserWallet;

class TextToHtmlParser implements TextToHtmlParserInterface
{
    public function textToHtml(string $text): string
    {
        $currency = UserWallet::currencyName(Localization::getCurrency());
        $html = '';
        $mode = '';
        $row_n = 0;
        foreach (explode(PHP_EOL, $text) as $line) {
            $line = rtrim($line, "\r");
            //hide particular currency block
            $line = str_replace( "<p>$($currency)</p>", '<div>', $line);
            $line = preg_replace( '#(<p>\$\(.*?\)</p>)#i', '<div class="hidden">', $line);
            $line = str_replace('<p>$$</p>', '</div>', $line);

            if (str_starts_with($line, '***')) {
                $html .= "<h2>" . $this->parseLine(substr($line, 3)) . "</h2>";
            //hide particular currency block
            } elseif (str_starts_with($line, '$(')) {
                if (preg_match('#.*\$\((.*?)\).*$#', $line, $matches)) {
                    if (mb_strtoupper($matches[1]) === $currency) {
                        $html .= "<div>";
                    } else {
                        $html .= "<div class='hidden'>";
                    }
                }
            } elseif (str_starts_with($line, '$$')) {
                $html .= "</div>";
            //close currency block
            } elseif (str_starts_with($line, '<')) {
                $html .= $line;
            } elseif (str_starts_with($line, '~')) {
                $html .= "<div class='flex jc-center'>".$this->parseLine(substr($line, 1))."</div>";
            } elseif (str_starts_with($line, '**')) {
                $html .= "<h3>".$this->parseLine(substr($line, 2))."</h3>";
            } elseif (str_starts_with($line, '*')) {
                $html .= "<p class='smaller'>".$this->parseLine(substr($line, 1))."</p>";
            } elseif (str_starts_with($line, '---')) {
                if ($mode === 'table') {
                    $html .= "</tbody></table>";
                    $mode = '';
                    $row_n = 0;
                } else {
                    $html .= "<table><thead>";
                    $mode = 'table';
                }
            } elseif (empty(trim($line))) {
                $html .= "</br>";
            } else {
                if ($mode === 'table') {
                    $row_n++;
                    $cells = explode("|", $line);
                    array_shift($cells);
                    array_pop($cells);
                    $html .= "<tr>";
                    foreach ($cells as $cell) {
                        if ($row_n === 1) {
                            $html .= "<th>" . $this->textToHtml($cell) . "</th>";
                        } else {
                            $html .= "<td>" . $this->textToHtml($cell) . "</td>";
                        }
                    }
                    if ($row_n === 1) $html .= "</thead><tbody>";
                    $html .= "</tr>";
                } else {
                    $html .= "<p>" . $this->parseLine($line) . "</p>";
                }
            }
        }
        return $html;
    }

    public function parseLine(string $text): string
    {
        $text = str_replace('@@', '</span>', $text);
        if (preg_match('#(.*?)@\((.*?)\)(.*)$#', $text, $matches)) {
            return $matches[1].'<span style="'. $this->parseStyles($matches[2]) .'">' . $this->parseLine($matches[3]);
        }
        return $text;
    }

    public function parseStyles($styles_str): string
    {
        $composed_style ='';
        $styles = explode(';', trim($styles_str));
        foreach ($styles as $style) {
            if (str_starts_with($style, 'color')) {
                $composed_style .= 'color: '. (explode('=', $style)[1]?? '') . ';';
            } elseif (str_starts_with($style, 'style')) {
                $composed_style .= 'color: '. (explode('=', $style)[1]?? '') . ';';
            }
        }
        return $composed_style;
    }
}
