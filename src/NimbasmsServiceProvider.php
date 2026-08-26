<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Nimbasms\Nimbasms\Http\Client;

class NimbasmsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nimbasms.php', 'nimbasms');

        $this->app->singleton(Client::class, function (Application $app): Client {
            $config = $app->make(Repository::class);

            return new Client(
                baseUrl: rtrim((string) $config->get('nimbasms.base_url'), '/'),
                serviceId: (string) $config->get('nimbasms.service_id', ''),
                secretToken: (string) $config->get('nimbasms.secret_token', ''),
                timeout: (int) $config->get('nimbasms.timeout', 20),
                senderName: (string) $config->get('nimbasms.sender_name', ''),
            );
        });

        $this->app->singleton(Nimbasms::class, function (Application $app): Nimbasms {
            return new Nimbasms($app->make(Client::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/nimbasms.php' => config_path('nimbasms.php'),
        ], ['nimbasms', 'nimbasms-config']);
    }
}
