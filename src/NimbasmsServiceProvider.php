<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms;

use Illuminate\Support\ServiceProvider;
use Nimbasms\Nimbasms\Console\Commands\NimbasmsCommand;

class NimbasmsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nimbasms.php', 'nimbasms');

        $this->app->singleton(Nimbasms::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/nimbasms.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nimbasms');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'nimbasms');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/nimbasms.php' => config_path('nimbasms.php'),
        ], ['nimbasms', 'nimbasms-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/nimbasms'),
        ], ['nimbasms', 'nimbasms-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/nimbasms'),
        ], ['nimbasms', 'nimbasms-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/nimbasms'),
        ], ['nimbasms', 'nimbasms-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['nimbasms', 'nimbasms-migrations']);

        $this->commands([
            NimbasmsCommand::class,
        ]);
    }
}
