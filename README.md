<div align="center">
    <h1>Nimbasms</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/aristide/nimbasms"><img src="https://img.shields.io/packagist/v/aristide/nimbasms.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/aristide/nimbasms"><img src="https://img.shields.io/packagist/php-v/aristide/nimbasms.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/aristide/nimbasms"><img src="https://badge.laravel.cloud/badge/aristide/nimbasms?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/aristide/nimbasms/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/aristide/nimbasms/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/aristide/nimbasms"><img src="https://img.shields.io/packagist/dt/aristide/nimbasms.svg?style=flat-square" alt="Total Downloads"></a>
</p>



## Installation

You can install the package via Composer:

```bash
composer require aristide/nimbasms
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="nimbasms"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="nimbasms-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="nimbasms-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="nimbasms-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="nimbasms-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="nimbasms-assets"
```

## Usage

<!-- Add a basic usage example here. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Nimbasms! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Aristide](https://github.com/aristide)
- [All Contributors](../../contributors)

## License

Nimbasms is open-sourced software licensed under the [MIT license](LICENSE.md).
