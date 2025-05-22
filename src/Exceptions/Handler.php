<?php

namespace TwitchAnalytics\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
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
        error_log($e);
    }

    public function shouldReport(Throwable $e)
    {
        return true;
    }

    public function render($request, Throwable $e): SymfonyResponse
    {
        $debug = env('APP_DEBUG', false);

        $response = [
            'error' => $e->getMessage(),
        ];

        if ($debug) {
            $response['trace'] = collect($e->getTrace())->map(function ($trace) {
                return Arr::only($trace, ['file', 'line', 'function', 'class']);
            })->take(5); // Mostrar solo los primeros 5 para no saturar
        }

        return new JsonResponse(
            $response,
            500,
            ['Content-Type' => 'application/json']
        );
    }

    public function renderForConsole($output, Throwable $e)
    {
        // Imprime el error en consola si lo ejecutas por CLI
        fwrite(STDERR, (string) $e);
    }
}
