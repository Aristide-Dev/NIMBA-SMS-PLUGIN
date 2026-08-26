<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms;

use Nimbasms\Nimbasms\Data\WebhookPayload;
use Nimbasms\Nimbasms\Http\Client;
use Nimbasms\Nimbasms\Resources\Accounts;
use Nimbasms\Nimbasms\Resources\Contacts;
use Nimbasms\Nimbasms\Resources\Extensions;
use Nimbasms\Nimbasms\Resources\Groups;
use Nimbasms\Nimbasms\Resources\Messages;
use Nimbasms\Nimbasms\Resources\Purchases;
use Nimbasms\Nimbasms\Resources\SenderNames;
use Nimbasms\Nimbasms\Resources\Verifications;
use Nimbasms\Nimbasms\Resources\Webhooks;

class Nimbasms
{
    public function __construct(private readonly Client $client) {}

    public function accounts(): Accounts
    {
        return new Accounts($this->client);
    }

    /**
     * @return array<string, mixed>
     */
    public function account(): array
    {
        return $this->accounts()->get();
    }

    public function messages(): Messages
    {
        return new Messages($this->client);
    }

    /**
     * @param  string|list<string>  $to
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function sendSms(string|array $to, string $message, ?string $senderName = null): array
    {
        return $this->messages()->sendSms($to, $message, $senderName);
    }

    /**
     * @param  string|list<string>  $to
     * @param  array<int|string, scalar>  $variables
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function sendWhatsApp(string|array $to, string $templateName, array $variables = [], ?string $senderName = null): array
    {
        return $this->messages()->sendWhatsApp($to, $templateName, $variables, $senderName);
    }

    public function verifications(): Verifications
    {
        return new Verifications($this->client);
    }

    public function contacts(): Contacts
    {
        return new Contacts($this->client);
    }

    public function groups(): Groups
    {
        return new Groups($this->client);
    }

    public function senderNames(): SenderNames
    {
        return new SenderNames($this->client);
    }

    public function purchases(): Purchases
    {
        return new Purchases($this->client);
    }

    public function extensions(): Extensions
    {
        return new Extensions($this->client);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parseWebhook(array $payload): WebhookPayload
    {
        return $this->webhooks()->parse($payload);
    }
}
