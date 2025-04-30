<?php

declare(strict_types=1);

namespace TwitchAnalytics\Domain\Exceptions;

class UserNotFoundException extends \RuntimeException
{
    public function __construct(string $displayName)
    {
        parent::__construct("No user found with given name: {$displayName}");
    }
}
