<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
*/

$app->singleton(
    TwitchAnalytics\Domain\Time\TimeProvider::class,
    TwitchAnalytics\Infrastructure\Time\SystemTimeProvider::class
);

$app->singleton(
    TwitchAnalytics\Infrastructure\ApiClient\TwitchApiClientInterface::class,
    TwitchAnalytics\Infrastructure\ApiClient\FakeTwitchApiClient::class
);

$app->singleton(
    TwitchAnalytics\Domain\Interfaces\UserRepositoryInterface::class,
    function ($app) {
        return new TwitchAnalytics\Infrastructure\Repositories\ApiUserRepository(
            $app->make(TwitchAnalytics\Infrastructure\ApiClient\TwitchApiClientInterface::class)
        );
    }
);

$app->singleton(
    TwitchAnalytics\Application\Services\UserAccountService::class,
    function ($app) {
        return new TwitchAnalytics\Application\Services\UserAccountService(
            $app->make(TwitchAnalytics\Domain\Interfaces\UserRepositoryInterface::class),
            $app->make(TwitchAnalytics\Domain\Time\TimeProvider::class)
        );
    }
);

$app->singleton(
    TwitchAnalytics\Controllers\GetUserPlatformAge\UserNameValidator::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
*/

$app->middleware([
    // Add any global middleware here
]);

/*
|--------------------------------------------------------------------------
| Register Routes
|--------------------------------------------------------------------------
*/

$app->router->group(['prefix' => 'api'], function ($router) {
    require __DIR__.'/../routes/api.php';
});

return $app;
