<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/BaseCore.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    response()->json($e->getMessage());
}

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();
$app->withEloquent();

//configure
$app->configure('filters');
$app->configure('templates');
$app->configure('filesystems');
$app->configure('app');
$app->configure('database');
$app->configure('queue');
$app->configure('reasons');
$app->configure('mail');

/*
Register Container Bindings
------------------------------
*/
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton('filesystem', function ($app) {
    return $app->loadComponent('filesystems', 'Illuminate\Filesystem\FilesystemServiceProvider', 'filesystem');
});

$app->singleton('filesystem', function ($app) {
    return $app->loadComponent(
        'filesystems',
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        'filesystem'
    );
});

/*
Register Middleware
------------------------
*/
$app->middleware([
    App\Http\Middleware\CorsMiddleware::class,
//    App\Http\Middleware\AuthTokenMiddleware::class,
    \App\Http\Middleware\Localization::class,
    \App\Http\Middleware\ParamRequestTransform::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'authToken' => App\Http\Middleware\AuthTokenMiddleware::class,
    'inventoryAccess' => App\Http\Middleware\InventoryAccessMiddleware::class,
    'localization' => \App\Http\Middleware\Localization::class,
    'validateServiceToken' => \App\Http\Middleware\ValidateServiceToken::class

]);


/*
Register Service Providers
--------------------------------
*/
//catch all options request
$app->register(\App\Providers\CatchAllOptionsRequestsProvider::class);
$app->register(\Illuminate\Session\SessionServiceProvider::class);

//provider for enable email service
$app->register(App\Providers\AppServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);

#wkhtmltopdf
$app->register(Barryvdh\Snappy\LumenServiceProvider::class);

//register two service providers - original one and Lumen adapter
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);

//Route List
//$app->register(\Thedevsaddam\LumenRouteList\LumenRouteListServiceProvider::class);

//Gateway
$app->register(\App\Providers\GatewayServiceProvider::class);

//Domain
$app->register(\App\Providers\DomainServiceProvider::class);

$app->register(\App\Providers\ModelServiceProvider::class);

//External
$app->register(Arseto\LumenCQRS\Providers\CQRSServiceProvider::class);


//$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

//phone
$app->register(Propaganistas\LaravelPhone\PhoneServiceProvider::class);

//$app->register(Aws\Laravel\AwsServiceProvider::class);
//class_alias(Aws\Laravel\AwsFacade::class, 'AWS');

//sentry log
$app->register(\Sentry\SentryLaravel\SentryLumenServiceProvider::class);

//excel
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);

class_alias(Maatwebsite\Excel\Facades\Excel::class, 'Excel');

/*
alias
--------------
*/
class_alias(Illuminate\Support\Facades\Storage::class, 'Storage');


/*
Load The Application Routes
-----------------------------
*/
$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__ . '/../app/Http/routes.php';
});

return $app;
