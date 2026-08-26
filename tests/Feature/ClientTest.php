<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Nimbasms\Nimbasms\Exceptions\AuthenticationException;
use Nimbasms\Nimbasms\Exceptions\NotFoundException;
use Nimbasms\Nimbasms\Exceptions\RateLimitException;
use Nimbasms\Nimbasms\Exceptions\ValidationException;
use Nimbasms\Nimbasms\Facades\Nimbasms;
use Nimbasms\Nimbasms\Nimbasms as NimbasmsClient;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('resolves the singleton', function () {
    expect(app(NimbasmsClient::class))->toBeInstanceOf(NimbasmsClient::class);
    expect(app(NimbasmsClient::class))->toBe(app(NimbasmsClient::class));
});

it('merges the package config', function () {
    expect(config('nimbasms.base_url'))->toBe('https://api.nimbasms.com/v1');
    expect(config('nimbasms.sender_name'))->toBe('Nimba SMS');
});

it('publishes the configuration file', function () {
    $this->artisan('vendor:publish', ['--tag' => 'nimbasms-config'])
        ->assertSuccessful();

    expect(file_exists(config_path('nimbasms.php')))->toBeTrue();
});

it('retrieves account details including sms and whatsapp balances', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/accounts' => Http::response([
            'sid' => 'acc_123',
            'sms_balance' => 120,
            'whatsapp_balance' => 40,
            'balance' => 120,
        ], 200),
    ]);

    $account = Nimbasms::account();

    expect($account)
        ->toHaveKey('sms_balance', 120)
        ->toHaveKey('whatsapp_balance', 40);

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://api.nimbasms.com/v1/accounts'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('service-id:secret-token'));
    });
});

it('sends an sms message', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/messages' => Http::response([
            'messageid' => '11111111-1111-1111-1111-111111111111',
            'message_cost' => 1,
            'url' => 'https://api.nimbasms.com/v1/messages/11111111-1111-1111-1111-111111111111',
        ], 201),
    ]);

    $response = Nimbasms::sendSms('624000000', 'Hello from Nimba SMS');

    expect($response)->toHaveKey('messageid');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->method() === 'POST'
            && $body['channel'] === 'sms'
            && $body['to'] === ['624000000']
            && $body['message'] === 'Hello from Nimba SMS'
            && $body['sender_name'] === 'Nimba SMS';
    });
});

it('sends a whatsapp template message', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/messages' => Http::response([
            'messageid' => '22222222-2222-2222-2222-222222222222',
            'message_cost' => 1,
            'url' => 'https://api.nimbasms.com/v1/messages/22222222-2222-2222-2222-222222222222',
        ], 201),
    ]);

    Nimbasms::sendWhatsApp('624000000', 'commande_confirmee', ['Fodé', '45 000 GNF']);

    Http::assertSent(function ($request) {
        $body = $request->data();
        $variables = $body['template_variables']['body'] ?? null;
        $variables = is_object($variables) ? (array) $variables : $variables;

        return $request->method() === 'POST'
            && $body['channel'] === 'whatsapp'
            && $body['template_name'] === 'commande_confirmee'
            && $variables[1] === 'Fodé'
            && $variables[2] === '45 000 GNF';
    });
});

it('chunks recipients above the api limit of 30', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/messages' => Http::response([
            'messageid' => '33333333-3333-3333-3333-333333333333',
            'message_cost' => 1,
            'url' => 'https://api.nimbasms.com/v1/messages/33333333-3333-3333-3333-333333333333',
        ], 201),
    ]);

    $numbers = [];

    for ($i = 1; $i <= 31; $i++) {
        $numbers[] = sprintf('6240000%02d', $i);
    }

    $responses = Nimbasms::sendSms($numbers, 'Bulk hello');

    expect($responses)->toHaveCount(2);
    Http::assertSentCount(2);
});

it('retrieves a message by id', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/messages/11111111-1111-1111-1111-111111111111' => Http::response([
            'messageid' => '11111111-1111-1111-1111-111111111111',
            'sender_name' => 'Nimba SMS',
            'message' => 'Hello',
            'status' => 'sent',
        ], 200),
    ]);

    $message = Nimbasms::messages()->get('11111111-1111-1111-1111-111111111111');

    expect($message)->toHaveKey('status', 'sent');
});

it('requests and checks a verification code', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/verifications' => Http::response([
            'verificationid' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'code' => '1234',
            'message_cost' => 1,
            'url' => 'https://api.nimbasms.com/v1/verifications/aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        ], 201),
        'https://api.nimbasms.com/v1/verifications/aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa' => Http::response([
            'status' => 'approved',
        ], 200),
    ]);

    $created = Nimbasms::verifications()->request(
        to: '624000000',
        message: 'Votre code de vérification est <1234>',
        expiryTime: 5,
        channels: ['sms', 'whatsapp'],
        language: 'fr',
    );

    $checked = Nimbasms::verifications()->check($created['verificationid'], '1234');

    expect($created)->toHaveKey('verificationid')
        ->and($checked)->toHaveKey('status', 'approved');

    Http::assertSent(function ($request) {
        if (! str_ends_with($request->url(), '/verifications') || $request->method() !== 'POST') {
            return false;
        }

        $body = $request->data();

        return $body['channels'] === ['sms', 'whatsapp']
            && $body['message'] === 'Votre code de vérification est <1234>'
            && $body['expiry_time'] === 5;
    });
});

it('lists and creates contacts', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/contacts*' => Http::sequence()
            ->push([
                [
                    'contact_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                    'name' => 'Utilisateur Test',
                    'numero' => '624000000',
                ],
            ], 200)
            ->push([
                'contact_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'name' => 'Nouveau',
                'numero' => '624000001',
                'groups' => ['TestGroup'],
            ], 201),
    ]);

    $list = Nimbasms::contacts()->list(limit: 10, offset: 0);
    $created = Nimbasms::contacts()->create([
        'name' => 'Nouveau',
        'numero' => '624000001',
        'groups' => ['TestGroup'],
    ]);

    expect($list)->toBeArray()
        ->and($created)->toHaveKey('numero', '624000001');
});

it('lists groups, sender names and purchases', function () {
    Http::fake([
        'https://api.nimbasms.com/v1/groups*' => Http::response([
            'count' => 1,
            'next' => null,
            'previous' => null,
            'results' => [['name' => 'VIP', 'total_contact' => 3]],
        ], 200),
        'https://api.nimbasms.com/v1/sendernames*' => Http::response([
            'count' => 1,
            'results' => [['name' => 'Nimba SMS', 'status' => 'accepted']],
        ], 200),
        'https://api.nimbasms.com/v1/purchases*' => Http::response([
            'count' => 0,
            'results' => [],
        ], 200),
    ]);

    expect(Nimbasms::groups()->list()['count'])->toBe(1)
        ->and(Nimbasms::senderNames()->list()['results'][0]['name'])->toBe('Nimba SMS')
        ->and(Nimbasms::purchases()->list()['count'])->toBe(0);
});

it('manages extensions, actions and pricing plans', function () {
    $extensionId = 'dddddddd-dddd-dddd-dddd-dddddddddddd';
    $actionId = 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee';
    $planId = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    Http::fake(function ($request) use ($extensionId, $actionId, $planId) {
        $path = parse_url($request->url(), PHP_URL_PATH);
        $method = $request->method();

        return match (true) {
            $method === 'GET' && $path === '/v1/extensions' => Http::response(['count' => 0, 'results' => []], 200),
            $method === 'POST' && $path === '/v1/extensions' => Http::response(['extensionid' => $extensionId, 'name' => 'Acme'], 201),
            $method === 'GET' && $path === "/v1/extensions/{$extensionId}" => Http::response(['extensionid' => $extensionId, 'name' => 'Acme'], 200),
            $method === 'PATCH' && $path === "/v1/extensions/{$extensionId}" => Http::response(['extensionid' => $extensionId, 'description' => 'Updated'], 200),
            $method === 'POST' && $path === "/v1/extensions/{$extensionId}/publish" => Http::response(['status' => 'OK'], 202),
            $method === 'GET' && $path === "/v1/extensions/{$extensionId}/actions" => Http::response(['count' => 0, 'results' => []], 200),
            $method === 'POST' && $path === "/v1/extensions/{$extensionId}/actions" => Http::response(['actionid' => $actionId, 'name' => 'Send'], 201),
            $method === 'GET' && $path === "/v1/extensions/{$extensionId}/actions/{$actionId}" => Http::response(['actionid' => $actionId, 'name' => 'Send'], 200),
            $method === 'PATCH' && $path === "/v1/extensions/{$extensionId}/actions/{$actionId}" => Http::response(['actionid' => $actionId, 'name' => 'Send updated'], 200),
            $method === 'POST' && $path === "/v1/extensions/{$extensionId}/actions/{$actionId}/publish" => Http::response(['status' => 'OK'], 202),
            $method === 'DELETE' && $path === "/v1/extensions/{$extensionId}/actions/{$actionId}" => Http::response('', 204),
            $method === 'POST' && $path === "/v1/extensions/{$extensionId}/pricing-plans" => Http::response(['pricingplanid' => $planId, 'name' => 'Standard'], 201),
            $method === 'PATCH' && $path === "/v1/extensions/{$extensionId}/pricing-plans/{$planId}" => Http::response(['pricingplanid' => $planId, 'name' => 'Premium'], 200),
            $method === 'DELETE' && $path === "/v1/extensions/{$extensionId}/pricing-plans/{$planId}" => Http::response('', 204),
            default => Http::response(['detail' => "Unexpected {$method} {$path}"], 500),
        };
    });

    $extensions = Nimbasms::extensions();

    expect($extensions->list()['count'])->toBe(0);

    $created = $extensions->create([
        'name' => 'Acme',
        'category' => 'communication',
        'description' => 'Demo',
        'base_api_url' => 'https://api.acme.com',
        'auth_type' => 'none',
        'is_paid' => false,
    ]);

    expect($created)->toHaveKey('extensionid', $extensionId)
        ->and($extensions->get($extensionId))->toHaveKey('name', 'Acme')
        ->and($extensions->update($extensionId, ['description' => 'Updated']))->toHaveKey('description', 'Updated')
        ->and($extensions->publish($extensionId))->toHaveKey('status', 'OK');

    $actions = $extensions->actions($extensionId);

    expect($actions->list()['count'])->toBe(0)
        ->and($actions->create([
            'name' => 'Send',
            'method' => 'POST',
            'endpoint' => '/send',
            'description' => 'Send a message',
        ]))->toHaveKey('actionid', $actionId)
        ->and($actions->get($actionId))->toHaveKey('name', 'Send')
        ->and($actions->update($actionId, ['name' => 'Send updated']))->toHaveKey('name', 'Send updated')
        ->and($actions->publish($actionId))->toHaveKey('status', 'OK')
        ->and($actions->delete($actionId))->toBe([]);

    $plans = $extensions->pricingPlans($extensionId);

    expect($plans->create([
        'name' => 'Standard',
        'price' => '10000',
        'billing_period' => 'monthly',
        'features' => ['sms' => true],
    ]))->toHaveKey('pricingplanid', $planId)
        ->and($plans->update($planId, ['name' => 'Premium']))->toHaveKey('name', 'Premium')
        ->and($plans->delete($planId))->toBe([]);
});

it('uploads an extension logo', function () {
    $extensionId = 'dddddddd-dddd-dddd-dddd-dddddddddddd';
    $logo = sys_get_temp_dir().DIRECTORY_SEPARATOR.'nimbasms-logo.png';
    file_put_contents($logo, 'fake-png');

    Http::fake([
        "https://api.nimbasms.com/v1/extensions/{$extensionId}/logo" => Http::response([
            'extensionid' => $extensionId,
            'logo' => 'https://cdn.nimbasms.com/logo.png',
        ], 200),
    ]);

    $result = Nimbasms::extensions()->updateLogo($extensionId, $logo);

    expect($result)->toHaveKey('logo');

    @unlink($logo);
});

it('parses inbound webhook payloads', function () {
    $payload = Nimbasms::parseWebhook([
        'messageid' => '11111111-1111-1111-1111-111111111111',
        'status' => 'received',
        'contact' => '624000000',
        'metadata' => ['message_type' => 'API'],
    ]);

    expect($payload->messageId)->toBe('11111111-1111-1111-1111-111111111111')
        ->and($payload->status)->toBe('received')
        ->and($payload->contact)->toBe('624000000')
        ->and($payload->metadata)->toHaveKey('message_type', 'API');
});

it('maps api errors to typed exceptions', function (int $status, string $exception) {
    Http::fake([
        'https://api.nimbasms.com/v1/accounts' => Http::response(['detail' => 'Erreur'], $status),
    ]);

    expect(fn () => Nimbasms::account())->toThrow($exception, 'Erreur');
})->with([
    [401, AuthenticationException::class],
    [400, ValidationException::class],
    [404, NotFoundException::class],
    [429, RateLimitException::class],
]);

it('rejects a message without recipients', function () {
    expect(fn () => Nimbasms::sendSms([], 'Hello'))
        ->toThrow(InvalidArgumentException::class);
});
