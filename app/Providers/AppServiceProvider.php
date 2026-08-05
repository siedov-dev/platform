<?php

namespace App\Providers;

use App\TextToHtml\TextToHtmlParser;
use App\TextToHtml\TextToHtmlParserInterface;
use Butschster\Head\Contracts\MetaTags\MetaInterface;
use Butschster\Head\MetaTags\Meta;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind(MetaInterface::class, Meta::class);
        $this->app->singleton(TextToHtmlParserInterface::class, TextToHtmlParser::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Отключили автоматическое добавление префикса локали и принудительный HTTPS,
        // чтобы API отвечало на запросы без редиректов.
        
        // URL::defaults(['locale' => 'aa']); 
        // URL::forceScheme('https');
        
        //
        // Log::info('Incoming request: ' . request()->fullUrl());
    }
}