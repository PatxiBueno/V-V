<?php

declare(strict_types=1);

namespace TwitchAnalytics\Infrastructure\ApiClient;

use TwitchAnalytics\Domain\Models\User;

interface TwitchApiClientInterface
{
    public function getUserByDisplayName(string $displayName): ?User;
}
