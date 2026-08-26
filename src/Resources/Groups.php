<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Http\Client;
use Nimbasms\Nimbasms\Support\Pagination;

final readonly class Groups
{
    public function __construct(private Client $client) {}

    /**
     * @return array<string, mixed>
     */
    public function list(?int $limit = null, ?int $offset = null): array
    {
        /** @var array<string, mixed> $groups */
        $groups = $this->client->get('/groups', Pagination::query($limit, $offset));

        return $groups;
    }
}
