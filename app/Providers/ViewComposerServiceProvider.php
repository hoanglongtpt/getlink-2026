<?php

namespace App\Providers;

use App\Models\DownloadProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $providers = DownloadProvider::where('is_active', true)->orderBy('display_name')->get();
            $view->with('providers', $providers);
        });
    }
}
