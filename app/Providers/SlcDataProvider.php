<?php

namespace App\Providers;

use App\Models\Slc\Factories\AchievementDataConcreteFactory;
use App\Models\Slc\Factories\AchievementDataFactory;
use App\Models\Slc\Factories\SlcItemConcreteFactory;
use App\Models\Slc\Factories\SlcItemFactory;
use Illuminate\Support\ServiceProvider;

class SlcDataProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(AchievementDataFactory::class, AchievementDataConcreteFactory::class);
        $this->app->singleton(SlcItemFactory::class, SlcItemConcreteFactory::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function provides()
    {
        return [
            AchievementDataFactory::class,
            SlcItemFactory::class,
        ];
    }
}
