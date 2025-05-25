<?php

require_once __DIR__ . '/../vendor/autoload.php';
(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
$app = new Laravel\Lumen\Application(dirname(__DIR__));

use TwitchAnalytics\Controllers\EnrichedController;
use TwitchAnalytics\Controllers\RegisterController;
use TwitchAnalytics\Controllers\StreamController;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Controllers\TopsOfTheTopsController;
use TwitchAnalytics\Controllers\UserController;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\VerifyToken;
use TwitchAnalytics\Service\Enriched;
use TwitchAnalytics\Service\TopsOfTheTops;
use TwitchAnalytics\Validators\ApiKeyValidator;
use TwitchAnalytics\Validators\EnrichedValidator;
use TwitchAnalytics\Validators\EmailValidator;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;
use TwitchAnalytics\Validators\UserValidator;

$app->routeMiddleware([
    'auth.token' => VerifyToken::class,
]);
$app->withFacades();
// Registrar rutas
$app->router->group([], function ($router) {

    require __DIR__ . '/../routes/api.php';
});
// Inyección de dependencias, el manejador de excepciones para el entorno de producción
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    TwitchAnalytics\Exceptions\Handler::class
);
$app->singleton(VerifyToken::class, function () {
    return new VerifyToken(new MYSQLDBManager());
});
$app->singleton(RegisterController::class, function () {
    return new RegisterController(new MYSQLDBManager(), new EmailValidator());
});
$app->singleton(StreamController::class, function () {
    return new StreamController(new TwitchAPIManager());
});
$app->singleton(TokenController::class, function () {
    return new TokenController(new MYSQLDBManager(), new EmailValidator(), new ApiKeyValidator());
});
$app->singleton(UserController::class, function () {
    return new UserController(new TwitchAPIManager(), new UserValidator());
});

$app->singleton(TopsOfTheTops::class, function ($app) {
    return new TopsOfTheTops($app->make(TwitchAPIManager::class), $app->make(MYSQLDBManager::class));
});
$app->singleton(Enriched::class, function ($app) {
    return new Enriched($app->make(TwitchAPIManager::class));
});
return $app;
