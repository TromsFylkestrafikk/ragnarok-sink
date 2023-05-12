<?php

namespace Tromsfylkestrafikk\RagnarokSink;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RagnarokSinkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ragnarok_sink.php', 'ragnaroksink');

        $this->publishConfig();

        // $this->loadViewsFrom(__DIR__.'/resources/views', 'ragnaroksink');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
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
            'namespace'  => "Tromsfylkestrafikk\RagnarokSink\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register facade
        // $this->app->singleton('ragnaroksink', function () {
        //     return new RagnarokSink();
        // });
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
                __DIR__ . '/../config/RagnarokSink.php' => config_path('ragnarok_sink.php'),
            ], 'config');
        }
    }
}
