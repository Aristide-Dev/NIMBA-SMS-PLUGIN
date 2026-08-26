<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use Nimbasms\Nimbasms\Http\Client;

final readonly class Verifications
{
    public function __construct(private Client $client) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $payload['sender_name'] ??= $this->client->senderName;

        /** @var array<string, mixed> $verification */
        $verification = $this->client->post('/verifications', $payload);

        return $verification;
    }

    /**
     * @param  string|list<string>  $channels
     * @return array<string, mixed>
     */
    public function request(
        string $to,
        ?string $message = null,
        ?int $expiryTime = null,
        ?int $attempts = null,
        ?int $codeLength = null,
        string|array $channels = ['sms'],
        ?string $senderName = null,
        ?string $whatsappSenderName = null,
        string $language = 'fr',
    ): array {
        $payload = [
            'to' => $to,
            'sender_name' => $senderName ?? $this->client->senderName,
            'channels' => is_array($channels) ? $channels : [$channels],
            'language' => $language,
        ];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($expiryTime !== null) {
            $payload['expiry_time'] = $expiryTime;
        }

        if ($attempts !== null) {
            $payload['attempts'] = $attempts;
        }

        if ($codeLength !== null) {
            $payload['code_length'] = $codeLength;
        }

        if ($whatsappSenderName !== null) {
            $payload['whatsapp_sender_name'] = $whatsappSenderName;
        }

        return $this->create($payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function check(string $verificationId, string|int $code): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->patch("/verifications/{$verificationId}", [
            'code' => (int) $code,
        ]);

        return $result;
    }
}
