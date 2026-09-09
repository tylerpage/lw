<?php

namespace App\Providers;

use App\Models\NavigationMenu;
use App\Models\SiteSetting;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AnalyticsService::class);
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $view->with([
                'headerMenu' => NavigationMenu::query()->where('location', 'header')->with('items.page')->first(),
                'footerMenu' => NavigationMenu::query()->where('location', 'footer')->with('items.page')->first(),
                'siteName' => SiteSetting::get('site_name', config('app.name')),
                'analyticsConfig' => app(AnalyticsService::class)->scriptConfig(),
            ]);
        });
    }
}
