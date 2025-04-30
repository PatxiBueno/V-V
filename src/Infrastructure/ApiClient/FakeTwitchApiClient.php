<?php

declare(strict_types=1);

namespace TwitchAnalytics\Infrastructure\ApiClient;

use TwitchAnalytics\Domain\Models\User;

class FakeTwitchApiClient implements TwitchApiClientInterface
{
    private array $fakeUsers = [
        [
            'id' => '12345',
            'login' => 'ninja',
            'display_name' => 'Ninja',
            'type' => '',
            'broadcaster_type' => 'partner',
            'description' => 'Professional Gamer and Streamer',
            'profile_image_url' => 'https://example.com/ninja.jpg',
            'offline_image_url' => 'https://example.com/ninja-offline.jpg',
            'view_count' => 500000,
            'created_at' => '2011-11-20T00:00:00Z'
        ],
        [
            'id' => '67890',
            'login' => 'pokimane',
            'display_name' => 'Pokimane',
            'type' => '',
            'broadcaster_type' => 'partner',
            'description' => 'Variety Streamer & Content Creator',
            'profile_image_url' => 'https://example.com/pokimane.jpg',
            'offline_image_url' => 'https://example.com/pokimane-offline.jpg',
            'view_count' => 400000,
            'created_at' => '2013-03-15T00:00:00Z'
        ]
    ];

    public function getUserByDisplayName(string $displayName): ?User
    {
        foreach ($this->fakeUsers as $userData) {
            if (strcasecmp($userData['display_name'], $displayName) === 0) {
                return new User(
                    $userData['id'],
                    $userData['login'],
                    $userData['display_name'],
                    $userData['type'],
                    $userData['broadcaster_type'],
                    $userData['description'],
                    $userData['profile_image_url'],
                    $userData['offline_image_url'],
                    $userData['view_count'],
                    $userData['created_at']
                );
            }
        }
        return null;
    }
}
