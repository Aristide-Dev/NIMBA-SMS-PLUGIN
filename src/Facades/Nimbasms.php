<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nimbasms\Nimbasms\Nimbasms
 */
class Nimbasms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nimbasms\Nimbasms\Nimbasms::class;
    }
}
