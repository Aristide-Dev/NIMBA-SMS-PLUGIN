<?php

declare(strict_types=1);

namespace Nimbasms\Nimbasms\Facades;

use Illuminate\Support\Facades\Facade;
use Nimbasms\Nimbasms\Data\WebhookPayload;
use Nimbasms\Nimbasms\Resources\Accounts;
use Nimbasms\Nimbasms\Resources\Contacts;
use Nimbasms\Nimbasms\Resources\Extensions;
use Nimbasms\Nimbasms\Resources\Groups;
use Nimbasms\Nimbasms\Resources\Messages;
use Nimbasms\Nimbasms\Resources\Purchases;
use Nimbasms\Nimbasms\Resources\SenderNames;
use Nimbasms\Nimbasms\Resources\Verifications;
use Nimbasms\Nimbasms\Resources\Webhooks;

/**
 * @method static Accounts accounts()
 * @method static array<string, mixed> account()
 * @method static Messages messages()
 * @method static array<string, mixed>|list<array<string, mixed>> sendSms(string|array<int, string> $to, string $message, ?string $senderName = null)
 * @method static array<string, mixed>|list<array<string, mixed>> sendWhatsApp(string|array<int, string> $to, string $templateName, array<int|string, scalar> $variables = [], ?string $senderName = null)
 * @method static Verifications verifications()
 * @method static Contacts contacts()
 * @method static Groups groups()
 * @method static SenderNames senderNames()
 * @method static Purchases purchases()
 * @method static Extensions extensions()
 * @method static Webhooks webhooks()
 * @method static WebhookPayload parseWebhook(array<string, mixed> $payload)
 *
 * @see \Nimbasms\Nimbasms\Nimbasms
 */
class Nimbasms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nimbasms\Nimbasms\Nimbasms::class;
    }
}
