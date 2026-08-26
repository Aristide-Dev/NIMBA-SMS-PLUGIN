<div align="center">
    <h1>Nimbasms</h1>
</div>

<p align="center">
    Client Laravel pour l'<a href="https://developers.nimbasms.com/">API Nimba SMS</a> : SMS, WhatsApp (templates Meta), OTP, contacts, groupes, sender names, achats et extensions.
</p>

<p align="center">
    <a href="https://packagist.org/packages/aristide/nimbasms"><img src="https://img.shields.io/packagist/v/aristide/nimbasms.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/aristide/nimbasms"><img src="https://img.shields.io/packagist/php-v/aristide/nimbasms.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://badge.laravel.cloud/badge/aristide/nimbasms?style=flat"><img src="https://badge.laravel.cloud/badge/aristide/nimbasms?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/aristide/nimbasms/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/aristide/nimbasms/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/aristide/nimbasms"><img src="https://img.shields.io/packagist/dt/aristide/nimbasms.svg?style=flat-square" alt="Total Downloads"></a>
</p>

## Prérequis

- PHP 8.3+
- Laravel 12 ou 13
- Un compte [Nimba SMS](https://www.nimbasms.com/?open-auth-modal&form=register) et des clés API (`service_id` / `secret_token`) depuis [API Keys](https://www.nimbasms.com/my-space/api-keys)

## Installation

```bash
composer require aristide/nimbasms
```

Publiez le fichier de configuration :

```bash
php artisan vendor:publish --tag="nimbasms-config"
```

Ajoutez vos identifiants dans `.env` :

```env
NIMBASMS_SERVICE_ID=votre-service-id
NIMBASMS_SECRET_TOKEN=votre-secret-token
NIMBASMS_SENDER_NAME="Nimba SMS"
```

L'authentification suit la spec officielle : HTTP Basic `base64(service_id:secret_token)` vers `https://api.nimbasms.com/v1`.

| Clé | Description |
| --- | --- |
| `NIMBASMS_BASE_URL` | URL de base (`https://api.nimbasms.com/v1` par défaut) |
| `NIMBASMS_SERVICE_ID` | Identifiant de service |
| `NIMBASMS_SECRET_TOKEN` | Jeton secret |
| `NIMBASMS_SENDER_NAME` | Nom d'expéditeur par défaut (sensible à la casse) |
| `NIMBASMS_TIMEOUT` | Timeout HTTP en secondes (`20` par défaut) |

## Usage

Toutes les réponses JSON de l'API sont renvoyées sous forme de tableaux PHP. Les erreurs HTTP lèvent une exception typée.

```php
use Nimbasms\Nimbasms\Facades\Nimbasms;
use Nimbasms\Nimbasms\Exceptions\NimbaSmsException;

try {
    $account = Nimbasms::account();
} catch (NimbaSmsException $exception) {
    report($exception);
}
```

### Compte

`GET /accounts` — soldes SMS et WhatsApp (`sms_balance`, `whatsapp_balance` ; `balance` est obsolète).

```php
$account = Nimbasms::account();

$account['sms_balance'];
$account['whatsapp_balance'];
```

### Messages SMS

`POST /messages` avec `channel: sms`. Maximum 30 destinataires par requête ; le client découpe automatiquement au-delà.

```php
Nimbasms::sendSms('624000000', 'Bonjour depuis Nimba SMS');

Nimbasms::sendSms(['624000000', '625000000'], 'Message groupé', 'MASOCIETE');
```

Jusqu'à 7 parties SMS (1071 caractères GSM-7). Simulateur : [simulator.nimbasms.com](https://simulator.nimbasms.com/).

### Messages WhatsApp

`POST /messages` avec `channel: whatsapp`. Le texte libre n'est pas accepté : il faut un **template Meta validé** créé depuis le dashboard Nimba SMS.

Les clés `"1"`, `"2"`, `"3"` correspondent à `{{1}}`, `{{2}}`, `{{3}}` dans le corps du template.

```php
Nimbasms::sendWhatsApp('624000000', 'commande_confirmee', [
    'Fodé',
    '45 000 GNF',
]);

Nimbasms::messages()->send([
    'channel' => 'whatsapp',
    'to' => ['624000000'],
    'template_name' => 'commande_confirmee',
    'template_variables' => [
        'body' => [
            '1' => 'Fodé',
            '2' => '45 000 GNF',
        ],
    ],
]);
```

### Détail d'un message

`GET /messages/{messageid}`

```php
Nimbasms::messages()->get('11111111-1111-1111-1111-111111111111');
```

### Vérifications OTP

`POST /verifications` puis `PATCH /verifications/{verificationid}`.

Le message SMS doit contenir le motif `<1234>`. Pour WhatsApp, le contenu est remplacé par le template d'authentification. Préférez cet endpoint aux SMS OTP via `/messages` : Nimba lui donne une file prioritaire.

```php
$verification = Nimbasms::verifications()->request(
    to: '624000000',
    message: 'Votre code de vérification est <1234>',
    expiryTime: 5,
    attempts: 3,
    codeLength: 4,
    channels: ['sms', 'whatsapp'],
    language: 'fr',
);

$result = Nimbasms::verifications()->check(
    $verification['verificationid'],
    '1234',
);

$result['status']; // approved, expired, too_many_attemps, ...
```

### Contacts

`GET /contacts` et `POST /contacts`

```php
Nimbasms::contacts()->list(limit: 50, offset: 0);

Nimbasms::contacts()->create([
    'name' => 'Utilisateur Test',
    'numero' => '624000000',
    'groups' => ['TestGroup'],
]);
```

### Groupes, sender names, achats

```php
Nimbasms::groups()->list();
Nimbasms::senderNames()->list();
Nimbasms::purchases()->list();
```

### Extensions marketplace

Couvre la spec OpenAPI : liste, création, détail, mise à jour, publication, logo, actions et plans tarifaires.

```php
$extensions = Nimbasms::extensions();

$extension = $extensions->create([
    'name' => 'Acme',
    'category' => 'communication',
    'description' => 'Intégration Acme',
    'base_api_url' => 'https://api.acme.com',
    'auth_type' => 'none',
    'is_paid' => false,
]);

$extensions->updateLogo($extension['extensionid'], storage_path('app/logo.png'));
$extensions->publish($extension['extensionid']);

$actions = $extensions->actions($extension['extensionid']);
$actions->create([
    'name' => 'Send',
    'method' => 'POST',
    'endpoint' => '/send',
    'description' => 'Envoyer un message',
]);
```

### Webhooks de livraison

L'URL se configure dans le dashboard Nimba (API Keys). Nimba envoie un `POST` JSON ; répondez `200 OK` (3 tentatives sinon).

```php
use Illuminate\Http\Request;
use Nimbasms\Nimbasms\Facades\Nimbasms;

Route::post('/webhooks/nimbasms', function (Request $request) {
    $payload = Nimbasms::parseWebhook($request->all());

    // $payload->messageId, $payload->status, $payload->contact

    return response()->json(['status' => 'OK']);
});
```

Statuts documentés côté livraison : `tosend`, `sent`, `received`, `read`, `failure`, `not_available`. Le webhook OpenAPI mentionne aussi `failed`.

## Exceptions

| HTTP | Exception |
| --- | --- |
| 400 / 422 | `Nimbasms\Nimbasms\Exceptions\ValidationException` |
| 401 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 420 / 429 | `RateLimitException` |
| 5xx | `ServerException` |
| autre | `NimbaSmsException` |

Toutes étendent `NimbaSmsException` (`$exception->status`, `$exception->body`).

L'API limite les envois à 300 SMS / seconde. Prévoyez un retry sur `RateLimitException`.

## Tests dans l'application hôte

```php
use Illuminate\Support\Facades\Http;
use Nimbasms\Nimbasms\Facades\Nimbasms;

Http::fake([
    'https://api.nimbasms.com/v1/messages' => Http::response([
        'messageid' => '11111111-1111-1111-1111-111111111111',
        'message_cost' => 1,
        'url' => 'https://api.nimbasms.com/v1/messages/11111111-1111-1111-1111-111111111111',
    ], 201),
]);

Nimbasms::sendSms('624000000', 'Test');

Http::assertSentCount(1);
```

## Documentation officielle

- [developers.nimbasms.com](https://developers.nimbasms.com/)
- OpenAPI : `https://developers.nimbasms.com/openapi?lang=fr`
- [Changelog Nimba SMS](https://developers.nimbasms.com/changelog)

## Changelog

Voir [CHANGELOG](CHANGELOG.md).

## Contributing

Voir [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md).

## Security Vulnerabilities

Voir [.github/SECURITY.md](.github/SECURITY.md).

## Credits

- [Aristide](https://github.com/aristide)
- [All Contributors](../../contributors)

## License

Nimbasms is open-sourced software licensed under the [MIT license](LICENSE.md).
