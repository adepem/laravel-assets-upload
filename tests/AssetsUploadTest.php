<?php

namespace Adepem\AssetsUpload\Test;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

class AssetsUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('assets-upload.directories', ['public']);
    }

    /** * @test */
    public function it_returns_early_if_environment_is_not_setup()
    {
        config()->set('assets-upload.filesystem', null);

        $this->artisan("assets:upload")
            ->expectsOutput('laravel-assets-upload is not configured, please set up ASSETS_UPLOAD_FILESYSTEM')
            ->assertExitCode(COMMAND::SUCCESS);
    }

    /** * @test */
    public function it_fails_if_filesystem_does_not_exist()
    {
        config()->set('assets-upload.filesystem', 'foo');

        $this->artisan("assets:upload")
            ->expectsOutput('Error: Disk [foo] does not have a configured driver.')
            ->assertExitCode(Command::FAILURE);
    }

    /** * @test */
    public function it_fails_if_upload_throws_an_exception()
    {
        config()->set('assets-upload.filesystem', 'foo');
        $exception = new RuntimeException("Oops!");

        Storage::shouldReceive('disk->put')->andThrows($exception);

        $this
            ->artisan("assets:upload")
            ->expectsOutput("Error: Oops!")
            ->assertExitCode(Command::FAILURE);
    }

    /** * @test */
    public function it_fails_if_upload_fails()
    {
        config()->set('assets-upload.filesystem', 'foo');

        Storage::shouldReceive('disk->put')->andReturnFalse();

        $this
            ->artisan("assets:upload")
            ->expectsOutput("Error: Some assets were not uploaded correctly.")
            ->assertExitCode(Command::FAILURE);
    }

    /** * @test */
    public function it_fails_if_directory_does_not_exists()
    {
        config()->set('assets-upload.filesystem', 'foo');
        config()->set('assets-upload.directories', ['bar']);
        Storage::fake('foo');

        $this
            ->artisan("assets:upload")
            ->assertExitCode(Command::FAILURE);
    }

    /** * @test */
    public function it_uploads_assets_files_successfully()
    {
        config()->set('assets-upload.filesystem', 'foo');
        $storage = Storage::fake('foo');

        $this->assertFileExists($this->app->publicPath() . DIRECTORY_SEPARATOR . 'index.php');
        $storage->assertMissing('public/index.php');

        $this->artisan("assets:upload")
            ->assertExitCode(Command::SUCCESS);

        $storage->assertExists('public/index.php');
    }

    /** * @test */
    public function it_puts_js_files_on_the_filesystem_with_the_correct_options()
    {
        config()->set('assets-upload.filesystem', 'foo');
        config()->set('assets-upload.cache-control.js', 1337);

        $fileDouble = Mockery::mock(SplFileInfo::class, [
            'getPathname' => base_path() . '/public/foo/bar.js',
            'getExtension' => 'js',
            'getContents' => 'some awesome javascript',
        ]);
        File::shouldReceive('allFiles')->andReturn([$fileDouble]);

        $diskDouble = Mockery::mock(Filesystem::class);
        $diskDouble->shouldReceive('put')
            ->once()
            ->withSomeOfArgs('/public/foo/bar.js', [
                'CacheControl' => 'max-age=1337',
                'ContentType' => 'application/javascript',
                'ContentEncoding' => 'gzip'
            ]);

        Storage::shouldReceive('disk')->andReturn($diskDouble);

        $this->artisan("assets:upload")
            ->assertExitCode(Command::SUCCESS);
    }

    /** * @test */
    public function it_puts_css_files_on_the_filesystem_with_the_correct_options()
    {
        config()->set('assets-upload.filesystem', 'foo');
        config()->set('assets-upload.cache-control.css', 1337);

        $fileDouble = Mockery::mock(SplFileInfo::class, [
            'getPathname' => base_path() . '/public/foo/bar.css',
            'getExtension' => 'css',
            'getContents' => 'some awesome css',
        ]);
        File::shouldReceive('allFiles')->andReturn([$fileDouble]);

        $diskDouble = Mockery::mock(Filesystem::class);
        $diskDouble->shouldReceive('put')
            ->once()
            ->withSomeOfArgs('/public/foo/bar.css', [
                'CacheControl' => 'max-age=1337',
                'ContentType' => 'text/css',
                'ContentEncoding' => 'gzip'
            ]);

        Storage::shouldReceive('disk')->andReturn($diskDouble);

        $this->artisan("assets:upload")
            ->assertExitCode(Command::SUCCESS);
    }

    /** * @test */
    public function it_puts_woff2_files_on_the_filesystem_with_the_correct_options()
    {
        config()->set('assets-upload.filesystem', 'foo');
        config()->set('assets-upload.cache-control.woff2', 1337);

        $fileDouble = Mockery::mock(SplFileInfo::class, [
            'getPathname' => base_path() . '/public/foo/bar.woff2',
            'getExtension' => 'woff2',
            'getContents' => 'some awesome font',
        ]);
        File::shouldReceive('allFiles')->andReturn([$fileDouble]);

        $diskDouble = Mockery::mock(Filesystem::class);
        $diskDouble->shouldReceive('put')
            ->once()
            ->with('/public/foo/bar.woff2', 'some awesome font', [
                'CacheControl' => 'max-age=1337',
            ]);

        Storage::shouldReceive('disk')->andReturn($diskDouble);

        $this->artisan("assets:upload")
            ->assertExitCode(Command::SUCCESS);
    }

    /** * @test */
    public function it_puts_other_file_type_on_the_filesystem_with_no_options()
    {
        config()->set('assets-upload.filesystem', 'foo');

        $fileDouble = Mockery::mock(SplFileInfo::class, [
            'getPathname' => base_path() . '/public/foo.bar',
            'getExtension' => 'bar',
            'getContents' => 'foobar',
        ]);
        File::shouldReceive('allFiles')->andReturn([$fileDouble]);

        $diskDouble = Mockery::mock(Filesystem::class);
        $diskDouble->shouldReceive('put')
            ->once()
            ->with('/public/foo.bar', 'foobar', []);

        Storage::shouldReceive('disk')->andReturn($diskDouble);

        $this->artisan("assets:upload")
            ->assertExitCode(Command::SUCCESS);
    }
}
