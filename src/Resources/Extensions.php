<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use InvalidArgumentException;
use Nimbasms\Nimbasms\Http\Client;
use Nimbasms\Nimbasms\Support\Pagination;

final readonly class Extensions
{
    public function __construct(private Client $client) {}

    /**
     * @return array<string, mixed>
     */
    public function list(?int $limit = null, ?int $offset = null): array
    {
        /** @var array<string, mixed> $extensions */
        $extensions = $this->client->get('/extensions', Pagination::query($limit, $offset));

        return $extensions;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        /** @var array<string, mixed> $extension */
        $extension = $this->client->post('/extensions', $payload);

        return $extension;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $extensionId): array
    {
        /** @var array<string, mixed> $extension */
        $extension = $this->client->get("/extensions/{$extensionId}");

        return $extension;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $extensionId, array $payload): array
    {
        /** @var array<string, mixed> $extension */
        $extension = $this->client->patch("/extensions/{$extensionId}", $payload);

        return $extension;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function publish(string $extensionId, array $payload = ['is_published' => true]): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->post("/extensions/{$extensionId}/publish", $payload);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function updateLogo(string $extensionId, string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Logo file not found: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidArgumentException("Unable to read logo file: {$path}");
        }

        /** @var array<string, mixed> $extension */
        $extension = $this->client->upload(
            "/extensions/{$extensionId}/logo",
            'logo',
            $contents,
            basename($path),
        );

        return $extension;
    }

    public function actions(string $extensionId): ExtensionActions
    {
        return new ExtensionActions($this->client, $extensionId);
    }

    public function pricingPlans(string $extensionId): ExtensionPricingPlans
    {
        return new ExtensionPricingPlans($this->client, $extensionId);
    }
}
