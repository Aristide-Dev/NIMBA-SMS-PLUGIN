<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Tests;

use Nimbasms\Nimbasms\NimbasmsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            NimbasmsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('nimbasms.base_url', 'https://api.nimbasms.com/v1');
        $app['config']->set('nimbasms.service_id', 'service-id');
        $app['config']->set('nimbasms.secret_token', 'secret-token');
        $app['config']->set('nimbasms.sender_name', 'Nimba SMS');
        $app['config']->set('nimbasms.timeout', 20);
    }
}
