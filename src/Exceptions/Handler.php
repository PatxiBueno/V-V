<?php

namespace TwitchAnalytics\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Arr;
use Throwable;
use Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Handler implements ExceptionHandler
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function report(Throwable $e)
    {
        // Solo mostrar errores si APP_DEBUG está activado
        if (getenv('APP_DEBUG', false)) {
            error_log($e);
        }
    }

    public function shouldReport(Throwable $e)
    {
        return true;
    }

    public function render($request, Throwable $e): SymfonyResponse
    {
        return new JsonResponse(
            ['message' => 'Internal server error'],
            500,
            ['Content-Type' => 'application/json']
        );
    }

    public function renderForConsole($output, Throwable $e)
    {
        // Mostrar el error en consola si se ejecuta por CLI
        fwrite(STDERR, (string) $e);
    }
}
