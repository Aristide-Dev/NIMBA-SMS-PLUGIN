<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Http\Client;

final readonly class Accounts
{
    public function __construct(private Client $client) {}

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        /** @var array<string, mixed> $account */
        $account = $this->client->get('/accounts');

        return $account;
    }
}
