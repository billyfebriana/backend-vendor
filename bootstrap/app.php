    <?php

    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;

    return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            apiPrefix: 'api',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware) {
            // Middleware global:
            // $middleware->web(append: [
            //     \App\Http\Middleware\HandleInertiaRequests::class,
            //     \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // ]);

            // Middleware untuk group API (ini yang kamu butuhkan untuk CORS)
            $middleware->api(prepend: [
                // Ini akan memastikan middleware CORS berjalan di awal group 'api'
                \Illuminate\Http\Middleware\HandleCors::class, // <<< PASTIKAN INI ADA
            ]);

            // Tambahkan middleware khusus rute jika diperlukan
            // $middleware->alias([
            //     'auth' => \App\Http\Middleware\Authenticate::class,
            // ]);
        })
        ->withExceptions(function (Exceptions $exceptions) {
            //
        })->create();

    