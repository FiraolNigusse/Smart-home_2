<?php

namespace App\Providers;

use App\Services\SystemLogService;
use App\Services\ConfigChangeLogger;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Events\ConfigLoaded;

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
        $this->registerConfigEventListeners();
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

    protected function registerConfigEventListeners(): void
    {
        // Log config changes on boot (only in web context, not console)
        if (!$this->app->runningInConsole()) {
            $this->app->booted(function () {
                $logger = app(ConfigChangeLogger::class);
                $logger->logConfigChanges();
            });
        }
    }
}
