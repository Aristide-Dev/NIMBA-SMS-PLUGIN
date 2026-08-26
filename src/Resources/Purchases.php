<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Http\Client;
use Nimbasms\Nimbasms\Support\Pagination;

final readonly class Purchases
{
    public function __construct(private Client $client) {}

    /**
     * @return array<string, mixed>
     */
    public function list(?int $limit = null, ?int $offset = null): array
    {
        /** @var array<string, mixed> $purchases */
        $purchases = $this->client->get('/purchases', Pagination::query($limit, $offset));

        return $purchases;
    }
}
