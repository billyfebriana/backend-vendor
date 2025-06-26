<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use IlluminateSupport\ServiceProvider;
use League\Flysystem\Filesystem;

// --- PERUBAHAN DI SINI ---
// Kita akan menggunakan Client dan Adapter dari paket 'tystuyfzand'
// yang sudah terinstall di project kamu.
use SeaweedFS\Client;
use SeaweedFS\Filesystem\SeaweedFSAdapter;

class SeaweedFSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Di sini kita "mengajari" Laravel cara membuat driver 'seaweedfs'
        // menggunakan resep yang benar untuk paket 'tystuyfzand'.
        Storage::extend('seaweedfs', function ($app, $config) {

            // 1. Buat dulu client-nya dengan base_uri dari config
            $client = new Client([
                'base_uri' => $config['base_uri'],
            ]);

            // 2. Buat adapter-nya dengan client yang sudah dibuat
            $adapter = new SeaweedFSAdapter($client);

            // 3. Kembalikan filesystem baru dengan adapter tersebut
            return new Filesystem($adapter);
        });
    }
}
