<?php

namespace Adepem\AssetsUpload;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AssetsUploadServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-assets-upload')
            ->hasConfigFile()
            ->hasCommand(AssetsUpload::class);
    }
}
