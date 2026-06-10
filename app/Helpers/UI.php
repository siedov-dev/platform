<?php

namespace App\Helpers;

use App\TextToHtml\TextToHtmlParserInterface;
use Illuminate\Support\Facades\App;

class UI
{
    public static function renderMoney(float $value = null, string $currency = null) : string
    {
        $value = self::moneyStr($value);
        $a = explode('.', $value);
        $html = '<div class="money"><div class="money__int">' . e(number_format($a[0])) . '</div>';
        if (count($a) > 0) {
            $html .= '<div class="money__rest">.' . e($a[1]) . ($currency === null ? '' : '&nbsp;' . $currency) . '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    public static function renderSlcMoney(float $value = null, $show_dec = true) : string
    {
        $value = self::moneyStr($value);
        $a = explode('.', $value);
        $html = '<p>' . e($a[0]);
        if (count($a) > 0 && $show_dec) {
            $html .= '<span>.' . e($a[1]) . '</span>';
        }
        $html .= '</p>';
        return $html;
    }

    public static function bonusPad(string $title, string $value): string
    {
        return "
        <div class=\"bonus-pad-block\">
            <div class='title'>$title</div>
            <div class='value'>$value</div>
        </div>";
    }

    public static function moneyStr(?float $value = 0, string $thousand_separator = ""): string
    {
        $l = number_format($value, 0, '', $thousand_separator);
        $r = explode(".", "".$value)[1]??'';
        $r = str_pad($r, 2, '0');
        return "$l.$r";
    }

    public static function prettyAmount(float $val): float
    {
        if ($val < 1) {
            $val = floor($val * 10000) / 10000;
        } elseif ($val < 10) {
            $val = floor($val * 10) / 10;
        } elseif ($val < 80) {
            $val = floor($val);
        } else {
            $val = floor($val / 10) * 10;
        }
        return $val;
    }

    public static function getTheme(): string
    {
        return $_COOKIE['theme']?? 'dark';
    }

    /**
     * make color darker or brighter
     * @param string $hexColor #cdcdcd
     * @param float $percent -100 to 100
     * @return string format ffffff
     */
    public static function adjustColorBrightness(string $hexColor, float $percent): string
    {
        // Ensure the percent is within the valid range (-100 to 100)
        $percent = max(-100, min(100, $percent));

        // Remove the '#' from the beginning of the hex color
        $hexColor = ltrim($hexColor, '#');

        // Convert hex to RGB
        [$r, $g, $b] = sscanf($hexColor, "%02x%02x%02x");

        // Adjust brightness
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));

        // Convert RGB back to hex
        return sprintf("%02x%02x%02x", $r, $g, $b);
    }

    public static function textToHtml(string $text): string
    {
        return App::make(TextToHtmlParserInterface::class)->textToHtml($text);
    }
}
