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
}
