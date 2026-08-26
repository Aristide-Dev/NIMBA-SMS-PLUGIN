<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Http\Client;
use Nimbasms\Nimbasms\Support\Pagination;

final readonly class Contacts
{
    public function __construct(private Client $client) {}

    /**
     * @return array<string, mixed>|list<mixed>
     */
    public function list(?int $limit = null, ?int $offset = null): array
    {
        return $this->client->get('/contacts', Pagination::query($limit, $offset));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        /** @var array<string, mixed> $contact */
        $contact = $this->client->post('/contacts', $payload);

        return $contact;
    }
}
