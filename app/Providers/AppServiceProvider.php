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
        URL::defaults(['locale' => 'aa']); //default local that makes localization detect real local
        URL::forceScheme('https');
        //
//        Log::info('Incoming request: ' . request()->fullUrl());
    }
}
