<?php

namespace Ragnarok\Dummy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Ragnarok\Dummy\Sinks\SinkDummy;
use Ragnarok\Sink\Facades\SinkRegistrar;

class DummyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ragnarok_dummy.php', 'ragnarok_dummy');
        $this->publishConfig();

        SinkRegistrar::register(SinkDummy::class);

        // $this->loadViewsFrom(__DIR__.'/resources/views', 'ragnarok_dummy');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->registerRoutes();
    }

    /**
     * Publish Config
     *
     * @return void
     */
    public function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ragnarok_dummy.php' => config_path('ragnarok_dummy.php'),
            ], ['config', 'config-dummy', 'dummy']);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
    * Get route group configuration array.
    *
    * @return string[]
    */
    protected function routeConfiguration(): array
    {
        return [
            'namespace'  => "Ragnarok\Dummy\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }
}
