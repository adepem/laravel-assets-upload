# Publish your assets using an artisan command

[![Latest Version on Packagist](https://img.shields.io/packagist/v/adepem/laravel-assets-upload.svg?style=flat-square)](https://packagist.org/packages/adepem/laravel-assets-upload)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/adepem/laravel-assets-upload/run-tests?label=tests)](https://github.com/adepem/laravel-assets-upload/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/adepem/laravel-assets-upload/Check%20&%20fix%20styling?label=code%20style)](https://github.com/adepem/laravel-assets-upload/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/adepem/laravel-assets-upload.svg?style=flat-square)](https://packagist.org/packages/adepem/laravel-assets-upload)

## Installation

You can install the package via composer:

```bash
composer require adepem/laravel-assets-upload
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Adepem\AssetsUpload\AssetsUploadServiceProvider" --tag="laravel-assets-upload-config"
```

This is the contents of the published config file:

```php
return [
    'filesystem' => env('ASSETS_UPLOAD_FILESYSTEM', false),

    'cache-control' => [
        'css' => env('ASSETS_UPLOAD_CACHE_CONTROL_CSS', 604800), // 7 days
        'js' => env('ASSETS_UPLOAD_CACHE_CONTROL_JS', 604800), // 7 days
        'woff2' => env('ASSETS_UPLOAD_CACHE_CONTROL_WOFF2', 31536000), // 365 days
    ],

    'directories' => [
        'public/css',
        'public/js',
    ]
];
```

## Usage

```bash
php artisan assets:upload
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [sirdharma](https://github.com/sirdharma)
- [Seedockh](https://github.com/Seedockh)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
