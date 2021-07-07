<?php

namespace Adepem\AssetsUpload\Test;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
          'Adepem\AssetsUpload\AssetsUploadServiceProvider',
        ];
    }
}
