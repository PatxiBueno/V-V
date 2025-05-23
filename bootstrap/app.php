<?php

require_once __DIR__ . '/../vendor/autoload.php';
(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
$app = new Laravel\Lumen\Application(dirname(__DIR__));
use TwitchAnalytics\Middleware\VerifyToken;
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
return $app;
