<?php

namespace App\Providers;

use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('azure', function ($app, array $config) {
            $client = BlobServiceClient::fromConnectionString($config['connection_string']);
            $adapter = new AzureBlobStorageAdapter(
                $client->getContainerClient($config['container']),
                $config['prefix'] ?? '',
                isPublicContainer: $config['public'] ?? true,
            );

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config,
            );
        });
    }
}
