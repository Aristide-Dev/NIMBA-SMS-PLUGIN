<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Data;

final readonly class WebhookPayload
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $messageId,
        public string $status,
        public string $contact,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $metadata = $payload['metadata'] ?? [];

        return new self(
            messageId: (string) ($payload['messageid'] ?? $payload['message_id'] ?? ''),
            status: (string) ($payload['status'] ?? 'received'),
            contact: (string) ($payload['contact'] ?? ''),
            metadata: is_array($metadata) ? $metadata : [],
        );
    }
}
