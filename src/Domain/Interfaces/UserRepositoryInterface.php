<?php

declare(strict_types=1);

namespace TwitchAnalytics\Domain\Interfaces;

use TwitchAnalytics\Domain\Models\User;

interface UserRepositoryInterface
{
    public function findByDisplayName(string $displayName): ?User;
}
