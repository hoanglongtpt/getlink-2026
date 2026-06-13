<?php

namespace App\Providers;

use App\Models\DownloadProvider;
use App\Models\DownloadHistory;
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
            $providers = DownloadProvider::where('is_active', true)
                ->select('download_providers.*')
                ->selectSub(
                    DownloadHistory::selectRaw('COUNT(*)')
                        ->whereColumn('download_histories.provider', 'download_providers.slug'),
                    'downloads_count'
                )
                ->orderByDesc('downloads_count')
                ->orderBy('display_name')
                ->get();
            $view->with('providers', $providers);
        });
    }
}
