<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Http\Client;

final readonly class ExtensionPricingPlans
{
    public function __construct(
        private Client $client,
        private string $extensionId,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        /** @var array<string, mixed> $plan */
        $plan = $this->client->post("/extensions/{$this->extensionId}/pricing-plans", $payload);

        return $plan;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(string $pricingPlanId, array $payload): array
    {
        /** @var array<string, mixed> $plan */
        $plan = $this->client->patch(
            "/extensions/{$this->extensionId}/pricing-plans/{$pricingPlanId}",
            $payload,
        );

        return $plan;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function delete(string $pricingPlanId): array
    {
        return $this->client->delete("/extensions/{$this->extensionId}/pricing-plans/{$pricingPlanId}");
    }
}
