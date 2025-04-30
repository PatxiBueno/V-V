<?php

declare(strict_types=1);

namespace TwitchAnalytics\Infrastructure\Time;

use TwitchAnalytics\Domain\Time\TimeProvider;

class SystemTimeProvider implements TimeProvider
{
    public function now(): \DateTime
    {
        return new \DateTime();
    }
}
