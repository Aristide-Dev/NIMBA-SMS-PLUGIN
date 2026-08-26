<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Data\WebhookPayload;

final readonly class Webhooks
{
    /**
     * Parse an inbound Nimba SMS delivery notification.
     *
     * The webhook URL is configured in the Nimba dashboard (API Keys).
     * Your application must answer HTTP 200 so Nimba stops retrying (up to 3 attempts).
     *
     * @param  array<string, mixed>  $payload
     */
    public function parse(array $payload): WebhookPayload
    {
        return WebhookPayload::fromArray($payload);
    }
}
