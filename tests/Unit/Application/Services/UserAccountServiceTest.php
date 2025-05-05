<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Unit\Application\Services;

use DateTime;
use PHPUnit\Framework\TestCase;
use Mockery;
use TwitchAnalytics\Application\Services\UserAccountService;
use TwitchAnalytics\Domain\Interfaces\UserRepositoryInterface;
use TwitchAnalytics\Domain\Exceptions\UserNotFoundException;
use TwitchAnalytics\Domain\Time\TimeProvider;
use TwitchAnalytics\Domain\Models\User;

class UserAccountServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private TimeProvider $timeProvider;
    private UserAccountService $userAccountService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->timeProvider = Mockery::mock(TimeProvider::class);
        $this->userAccountService = new UserAccountService(
            $this->userRepository,
            $this->timeProvider
        );
    }

    /**
     * @test
     */
    public function errorThrownIfUserNotFound(): void
    {
        $displayName = 'NonExistentUser';
        $this->userRepository->expects('findByDisplayName')
            ->with($displayName)
            ->andReturnNull();

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("No user found with given name: {$displayName}");

        $this->userAccountService->getAccountAge($displayName);
    }

    /**
     * @test
     */
    public function getsAccountAge(): void
    {
        $displayName = 'TestUser';
        $createdAt = '2023-01-01T00:00:00Z';
        $oneYearSinceCreation = new DateTime('2024-01-01T00:00:00Z');

        $user = new User(
            '12345',
            'testuser',
            $displayName,
            '',
            'partner',
            'Test User Description',
            'https://example.com/test.jpg',
            'https://example.com/test-offline.jpg',
            100000,
            $createdAt
        );

        $this->userRepository->expects('findByDisplayName')
            ->with($displayName)
            ->andReturns($user);
        $this->timeProvider->expects('now')
            ->andReturns($oneYearSinceCreation);

        $accountAge = $this->userAccountService->getAccountAge($displayName);

        $this->assertEquals([
            'name' => $displayName,
            'days_since_creation' => 365,
            'created_at' => $createdAt
        ], $accountAge);
    }
}
