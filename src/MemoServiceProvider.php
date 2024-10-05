<?php

namespace Saidabdulsalam\LaravelMemo;
use Illuminate\Support\ServiceProvider;

class MemoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Register the controller and other services
        $this->app->make('Saidabdulsalam\LaravelMemo\Http\Controllers\MemoController');
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/memo.php' => config_path('memo.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'memo-config');

        // Load the migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load the routes
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

    }
}
