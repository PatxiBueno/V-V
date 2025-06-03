<?php

require_once __DIR__ . '/../vendor/autoload.php';
(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
$app = new Laravel\Lumen\Application(dirname(__DIR__));

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\TokenVerifyer;
use TwitchAnalytics\Service\EnrichedService;
use TwitchAnalytics\Service\RegisterService;
use TwitchAnalytics\Service\StreamsService;
use TwitchAnalytics\Service\TokenService;
use TwitchAnalytics\Service\TopsOfTheTopsService;
use TwitchAnalytics\Service\UserService;

use TwitchAnalytics\Validators\ApiKeyValidator;
use TwitchAnalytics\Validators\EmailValidator;
use TwitchAnalytics\Validators\EnrichedValidator;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;
use TwitchAnalytics\Validators\UserValidator;


$app->routeMiddleware([
    'auth.token' => TokenVerifyer::class,
]);
$app->withFacades();

$app->router->group([], function ($router) {

    require __DIR__ . '/../routes/api.php';
});

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    TwitchAnalytics\Exceptions\Handler::class
);
$app->singleton(MYSQLDBManager::class);
$app->singleton(TwitchAPIManager::class);
$app->singleton(EmailValidator::class);
$app->singleton(EnrichedValidator::class);
$app->singleton(ApiKeyValidator::class);
$app->singleton(TopsOfTheTopsValidator::class);
$app->singleton(UserValidator::class);

$app->singleton(TokenVerifyer::class, function ($app) {
    return new TokenVerifyer($app->make(MYSQLDBManager::class));
});
$app->singleton(TopsOfTheTopsService::class, function ($app) {
    return new TopsOfTheTopsService($app->make(TwitchAPIManager::class), $app->make(MYSQLDBManager::class));
});
$app->singleton(EnrichedService::class, function ($app) {
    return new EnrichedService($app->make(TwitchAPIManager::class));
});
$app->singleton(RegisterService::class, function ($app) {
    return new RegisterService($app->make(MYSQLDBManager::class));
});
$app->singleton(StreamsService::class, function ($app) {
    return new StreamsService($app->make(TwitchAPIManager::class));
});
$app->singleton(TokenService::class, function ($app) {
    return new TokenService($app->make(MYSQLDBManager::class));
});
$app->singleton(UserService::class, function ($app) {
    return new UserService($app->make(TwitchAPIManager::class));
});
return $app;
