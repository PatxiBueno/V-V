<?php

declare(strict_types=1);

namespace TwitchAnalytics\Infrastructure\Repositories;

use TwitchAnalytics\Domain\Interfaces\UserRepositoryInterface;
use TwitchAnalytics\Domain\Models\User;
use TwitchAnalytics\Infrastructure\ApiClient\TwitchApiClientInterface;

class ApiUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private TwitchApiClientInterface $apiClient
    ) {
    }

    public function findByDisplayName(string $displayName): ?User
    {
        return $this->apiClient->getUserByDisplayName($displayName);
    }
}
