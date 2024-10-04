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
        // Load the migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load the routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Publish the config file
        $this->publishes([
            __DIR__ . '/../config/memo.php' => config_path('memo.php'),
        ]);
    }
}
