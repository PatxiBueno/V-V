<?php

require_once __DIR__ . '/../vendor/autoload.php';
(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
$app = new Laravel\Lumen\Application(dirname(__DIR__));

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\VerifyToken;
use TwitchAnalytics\Service\Enriched;
use TwitchAnalytics\Service\Register;
use TwitchAnalytics\Service\Streams;
use TwitchAnalytics\Service\Token;
use TwitchAnalytics\Service\TopsOfTheTops;
use TwitchAnalytics\Service\User;

use TwitchAnalytics\Validators\ApiKeyValidator;
use TwitchAnalytics\Validators\EmailValidator;
use TwitchAnalytics\Validators\EnrichedValidator;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;
use TwitchAnalytics\Validators\UserValidator;


$app->routeMiddleware([
    'auth.token' => VerifyToken::class,
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

$app->singleton(VerifyToken::class, function ($app) {
    return new VerifyToken($app->make(MYSQLDBManager::class));
});
$app->singleton(TopsOfTheTops::class, function ($app) {
    return new TopsOfTheTops($app->make(TwitchAPIManager::class), $app->make(MYSQLDBManager::class));
});
$app->singleton(Enriched::class, function ($app) {
    return new Enriched($app->make(TwitchAPIManager::class));
});
$app->singleton(Register::class, function ($app) {
    return new Register($app->make(MYSQLDBManager::class));
});
$app->singleton(Streams::class, function ($app) {
    return new Streams($app->make(TwitchAPIManager::class));
});
$app->singleton(Token::class, function ($app) {
    return new Token($app->make(MYSQLDBManager::class));
});
$app->singleton(User::class, function ($app) {
    return new User($app->make(TwitchAPIManager::class));
});
return $app;
