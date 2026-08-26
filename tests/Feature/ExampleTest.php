<?php

declare(strict_types=1);

use Nimbasms\Nimbasms\Nimbasms;

it('resolves the singleton', function () {
    expect(app(Nimbasms::class))->toBeInstanceOf(Nimbasms::class);
});

it('returns the same instance from the container', function () {
    expect(app(Nimbasms::class))->toBe(app(Nimbasms::class));
});

it('merges the package config', function () {
    expect(config('nimbasms.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('nimbasms::messages.placeholder'))->toBe('Nimbasms placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('nimbasms::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('nimbasms:placeholder')
        ->expectsOutputToContain('Nimbasms placeholder command executed.')
        ->assertSuccessful();
});
