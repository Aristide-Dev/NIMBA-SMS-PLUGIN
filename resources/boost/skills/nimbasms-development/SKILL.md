---
name: nimbasms-development
description: >
  Configure and apply the aristide/nimbasms Laravel client for the Nimba SMS API
  (SMS, WhatsApp templates, OTP, contacts, groups, sender names, purchases, extensions, webhooks).
license: MIT
metadata:
  author: Aristide
---

# Nimbasms

Use this skill when a Laravel application needs to send SMS or WhatsApp messages, request OTP codes, or call any Nimba SMS API resource through `aristide/nimbasms`.

## Primary Goal

- apply the `aristide/nimbasms` public API in the smallest correct way

## Workflow

### 1. Inspect the Laravel app context

- confirm the app is a Laravel 12/13 project
- check whether `NIMBASMS_SERVICE_ID`, `NIMBASMS_SECRET_TOKEN`, and `NIMBASMS_SENDER_NAME` are already set
- prefer the facade `Nimbasms\Nimbasms\Facades\Nimbasms` at call sites

### 2. Install and configure

```bash
composer require aristide/nimbasms
php artisan vendor:publish --tag="nimbasms-config"
```

Set at least:

```env
NIMBASMS_SERVICE_ID=
NIMBASMS_SECRET_TOKEN=
NIMBASMS_SENDER_NAME=
```

### 3. Apply the package's public API

- Account: `Nimbasms::account()` — use `sms_balance` / `whatsapp_balance`, not obsolete `balance`
- SMS: `Nimbasms::sendSms($to, $message)` — `channel` is `sms`; max 30 recipients per HTTP call (auto-chunked)
- WhatsApp: `Nimbasms::sendWhatsApp($to, $templateName, $variables)` — Meta-approved template only, never free text
- OTP: `Nimbasms::verifications()->request(...)` then `check($id, $code)` — SMS body must include `<1234>`; WhatsApp uses the auth template
- Contacts / groups / sender names / purchases / extensions: `Nimbasms::contacts()`, `groups()`, `senderNames()`, `purchases()`, `extensions()`
- Inbound delivery webhook: `Nimbasms::parseWebhook($request->all())` then return HTTP 200 `{status: OK}`
- Errors: catch `Nimbasms\Nimbasms\Exceptions\NimbaSmsException` (or the 401/404/429 subclasses)

### 4. Tests in the host app

Fake `Illuminate\Support\Facades\Http` against `https://api.nimbasms.com/v1/...`. Do not hit the live API in tests.

## Rules, References, and Templates

Read before executing:

- `config/nimbasms.php` keys after publish
- Official docs: https://developers.nimbasms.com/
- OpenAPI: https://developers.nimbasms.com/openapi?lang=fr

## Examples

Send SMS and WhatsApp from a Laravel action:

```php
use Nimbasms\Nimbasms\Facades\Nimbasms;

Nimbasms::sendSms($user->phone, 'Votre commande est confirmée.');

Nimbasms::sendWhatsApp($user->phone, 'commande_confirmee', [
    $user->name,
    $order->total,
]);
```

Request an OTP on SMS + WhatsApp:

```php
$verification = Nimbasms::verifications()->request(
    to: $phone,
    message: 'Votre code de vérification est <1234>',
    channels: ['sms', 'whatsapp'],
);
```

## Anti-patterns

- do not send WhatsApp as a free-text `message`; `template_name` is required
- do not put OTP codes on `POST /messages`; use `POST /verifications`
- do not call `env()` in application code; read `config('nimbasms.*')`
- do not document or depend on package internals (`Http\Client`, resource classes used outside the facade) when the facade methods suffice
- do not swallow API errors as `['error' => ...]`; exceptions are the contract
