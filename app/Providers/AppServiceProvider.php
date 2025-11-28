<?php

namespace App\Providers;

use App\Services\SystemLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->logSystemEvents();
    }

    protected function logSystemEvents(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $cacheKey = 'system_log_last_boot';

        if (!Cache::has($cacheKey)) {
            app(SystemLogService::class)->log(
                eventType: 'system.boot',
                severity: 'info',
                actor: null,
                message: 'Application boot detected'
            );
            Cache::put($cacheKey, now(), 300);
        }

        register_shutdown_function(function () {
            app(SystemLogService::class)->log(
                eventType: 'system.shutdown',
                severity: 'info',
                actor: null,
                message: 'Application shutdown event'
            );
        });
    }
}
