<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Resources;

use InvalidArgumentException;
use Nimbasms\Nimbasms\Http\Client;
use stdClass;

final readonly class Messages
{
    public function __construct(private Client $client) {}

    /**
     * Send a message on any documented channel (`sms`, `whatsapp`, or `email`).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function send(array $payload): array
    {
        $payload['sender_name'] ??= $this->client->senderName;

        $recipients = $this->recipients($payload['to'] ?? []);

        if ($recipients === []) {
            throw new InvalidArgumentException('At least one recipient is required.');
        }

        $payload['to'] = $recipients;

        $responses = [];

        foreach (array_chunk($recipients, Client::MAX_RECIPIENTS) as $chunk) {
            $chunkPayload = $payload;
            $chunkPayload['to'] = $chunk;

            /** @var array<string, mixed> $response */
            $response = $this->client->post('/messages', $chunkPayload);
            $responses[] = $response;
        }

        return count($responses) === 1 ? $responses[0] : $responses;
    }

    /**
     * @param  string|list<string>  $to
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function sendSms(string|array $to, string $message, ?string $senderName = null): array
    {
        return $this->send([
            'channel' => 'sms',
            'to' => $to,
            'message' => $message,
            'sender_name' => $senderName ?? $this->client->senderName,
        ]);
    }

    /**
     * Send a WhatsApp message using a Meta-approved template.
     *
     * @param  string|list<string>  $to
     * @param  array<int|string, scalar>  $variables
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function sendWhatsApp(string|array $to, string $templateName, array $variables = [], ?string $senderName = null): array
    {
        return $this->send([
            'channel' => 'whatsapp',
            'to' => $to,
            'template_name' => $templateName,
            'template_variables' => [
                'body' => $this->templateBody($variables),
            ],
            'sender_name' => $senderName ?? $this->client->senderName,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $messageId): array
    {
        /** @var array<string, mixed> $message */
        $message = $this->client->get("/messages/{$messageId}");

        return $message;
    }

    /**
     * @param  array<int|string, scalar>  $variables
     */
    private function templateBody(array $variables): stdClass
    {
        $body = new stdClass;

        if (array_is_list($variables)) {
            foreach ($variables as $index => $value) {
                $body->{(string) ($index + 1)} = (string) $value;
            }

            return $body;
        }

        foreach ($variables as $key => $value) {
            $body->{(string) $key} = (string) $value;
        }

        return $body;
    }

    /**
     * @param  string|list<string>|mixed  $to
     * @return list<string>
     */
    private function recipients(mixed $to): array
    {
        $numbers = array_values(array_filter(
            array_map(static fn (mixed $number): string => is_scalar($number) ? (string) $number : '', is_array($to) ? $to : [$to]),
            static fn (string $number): bool => $number !== '',
        ));

        return $numbers;
    }
}
