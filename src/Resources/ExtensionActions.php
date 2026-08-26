<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Http\Client;
use Nimbasms\Nimbasms\Support\Pagination;

final readonly class ExtensionActions
{
    public function __construct(
        private Client $client,
        private string $extensionId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function list(?int $limit = null, ?int $offset = null): array
    {
        /** @var array<string, mixed> $actions */
        $actions = $this->client->get(
            "/extensions/{$this->extensionId}/actions",
            Pagination::query($limit, $offset),
        );

        return $actions;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        /** @var array<string, mixed> $action */
        $action = $this->client->post("/extensions/{$this->extensionId}/actions", $payload);

        return $action;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $actionId): array
    {
        /** @var array<string, mixed> $action */
        $action = $this->client->get("/extensions/{$this->extensionId}/actions/{$actionId}");

        return $action;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $actionId, array $payload): array
    {
        /** @var array<string, mixed> $action */
        $action = $this->client->patch("/extensions/{$this->extensionId}/actions/{$actionId}", $payload);

        return $action;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function delete(string $actionId): array
    {
        return $this->client->delete("/extensions/{$this->extensionId}/actions/{$actionId}");
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function publish(string $actionId, array $payload = ['is_published' => true]): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->post(
            "/extensions/{$this->extensionId}/actions/{$actionId}/publish",
            $payload,
        );

        return $result;
    }
}
