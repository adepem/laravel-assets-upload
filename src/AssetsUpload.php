<?php

namespace Adepem\AssetsUpload;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class AssetsUpload extends Command
{
    protected $signature = 'assets:upload';
    protected $description = 'Upload all assets to filesystem';

    const CONTENT_TYPES = [
        'css' => 'text/css',
        'js' => 'application/javascript',
    ];

    static private function cacheControl(string $extension): int
    {
        return [
            'css' => config('assets-upload.cache-control.css'),
            'js' => config('assets-upload.cache-control.js'),
            'woff2' => config('assets-upload.cache-control.woff2'),
        ][$extension];
    }

    public function handle(): int
    {
        $this->output->title('Uploading assets');

        if ($this->filesystemIsSet() === false) {
            $this->warn("laravel-assets-upload is not configured, please set up ASSETS_UPLOAD_FILESYSTEM");
            return self::SUCCESS;
        }

        try {
            $disk = Storage::disk(config('assets-upload.filesystem'));
            $failedUploads = $this->uploadDirectoriesToDisk($disk);
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return self::FAILURE;
        }

        if (count($failedUploads) > 0) {
            $this->error("Error: Some assets were not uploaded correctly.");
            return self::FAILURE;
        }

        $this->info("Success: All assets were successfully uploaded.");
        return self::SUCCESS;
    }

    private function filesystemIsSet(): bool
    {
        return config('assets-upload.filesystem', false) ?? false;
    }

    private function filesToUpload(): Collection
    {
        return collect(config('assets-upload.directories'))->flatMap(function ($directory) {
            return File::allFiles($this->laravel->basePath($directory));
        });
    }

    private function uploadDirectoriesToDisk($disk): array
    {
        $failedUploads = [];

        $this->withProgressBar(
            $this->filesToUpload(),
            function ($file) use ($disk, &$failedUploads) {
                $path = $this->getRelativePathnameForFile($file);
                $fileContent = $this->getFileContentForFile($file);
                $options = $this->getOptionsForFileExtension($file);

                if ($disk->put($path, $fileContent, $options) === false) {
                    $failedUploads[] = $path;
                }
            }
        );

        $this->newLine(2);

        return $failedUploads;
    }

    private function getRelativePathnameForFile(SplFileInfo $file): string
    {
        return Str::of($file->getPathname())->replaceFirst(base_path(), '');
    }

    private function getFileContentForFile(SplFileInfo $file): string
    {
        $fileContent = $file->getContents();
        $fileExtension = $file->getExtension();

        if ($fileExtension === 'css' || $fileExtension === 'js') {
            return gzencode($fileContent);
        }

        return $fileContent;
    }

    private function getOptionsForFileExtension(SplFileInfo $file): array
    {
        $fileExtension = $file->getExtension();

        if ($fileExtension === 'css' || $fileExtension === 'js') {
            return [
                'CacheControl' => 'max-age=' . self::cacheControl($fileExtension),
                'ContentType' => self::CONTENT_TYPES[$fileExtension],
                'ContentEncoding' => 'gzip',
            ];
        }

        if (Str::of($fileExtension)->test('/^woff2?$/')) {
            return [
                'CacheControl' => 'max-age=' . self::cacheControl('woff2'),
            ];
        }

        return [];
    }
}
