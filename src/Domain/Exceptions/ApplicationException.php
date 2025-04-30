<?php

declare(strict_types=1);

namespace TwitchAnalytics\Domain\Exceptions;

abstract class ApplicationException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
