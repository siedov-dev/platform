<?php

use Butschster\Head\MetaTags\Viewport;

return [
    /*
     * Meta title section
     */
    'title' => [
        'default' => 'Кракен 🐙 Онлайн казино', // env('APP_NAME'),
        'separator' => '-',
        'max_length' => 255,
    ],


    /*
     * Meta description section
     */
    'description' => [
        'default' => 'Онлайн казино Кракен',
        'max_length' => 255,
    ],


    /*
     * Meta keywords section
     */
    'keywords' => [
        'default' => 'Онлайн казино, Кракен',
        'max_length' => 255
    ],

    /*
     * Default packages
     *
     * Packages, that should be included everywhere
     */
    'packages' => [
        // 'bootstrap', 'jquery', ...
    ],

    'charset' => 'UTF-8',
    'robots' => null,
    'viewport' => Viewport::RESPONSIVE,
    /* 'viewport' => 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no', */
    'csrf_token' => true,

    'format-detection' => [
        'default' => 'telephone=no',
        'max_length' => 255
    ],
];