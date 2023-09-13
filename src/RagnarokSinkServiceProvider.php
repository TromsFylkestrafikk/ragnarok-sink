<?php

namespace Ragnarok\Sink;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RagnarokSinkServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ragnarok_sink.php', 'ragnarok_sink');
        // Register facade
        // $this->app->singleton('ragnaroksink', function () {
        //     return new RagnarokSink();
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web/routes.php');
        });
    }

    /**
    * Get route group configuration array.
    *
    * @return array
    */
    private function routeConfiguration()
    {
        return [
            'namespace'  => "Ragnarok\Sink\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api',
        ];
    }

    /**
     * Publish Config
     *
     * @return void
     */
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ragnarok_sink.php' => config_path('ragnarok_sink.php'),
            ], 'config');
        }
    }
}
