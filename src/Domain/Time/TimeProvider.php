<?php

declare(strict_types=1);

namespace TwitchAnalytics\Domain\Time;

interface TimeProvider
{
    public function now(): \DateTime;
}
