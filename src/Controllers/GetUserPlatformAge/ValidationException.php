<?php

declare(strict_types=1);

namespace TwitchAnalytics\Controllers\GetUserPlatformAge;

use TwitchAnalytics\Domain\Exceptions\ApplicationException;

class ValidationException extends ApplicationException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
