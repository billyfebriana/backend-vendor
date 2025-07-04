<?php

namespace App\Providers;

// Pastikan namespace ini benar, tidak ada typo
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;

// 'use' statement yang benar untuk paket 'tystuyfzand' yang sudah terinstall
use SeaweedFS\Client;
use SeaweedFS\Filesystem\SeaweedFSAdapter;

class SeaweedFSServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Biarkan kosong
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan driver 'seaweedfs' ke Storage Laravel.
        Storage::extend('seaweedfs', function ($app, $config) {

            // 1. Buat instance Client untuk koneksi ke master server.
            $client = new Client([
                'base_uri' => $config['base_uri'],
            ]);

            // 2. Buat Adapter Flysystem dengan client yang sudah dibuat.
            $adapter = new SeaweedFSAdapter($client);

            // 3. Kembalikan instance Filesystem yang siap pakai.
            return new Filesystem($adapter);
        });
    }
}
