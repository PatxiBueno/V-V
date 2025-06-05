<?php

namespace TwitchAnalytics\Tests\Unit\Service;

use Mockery;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\TokenService;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    private $tokenService;
    private $dbManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbManager = Mockery::mock(MYSQLDBManager::class);
        $this->tokenService = new TokenService($this->dbManager);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function generatesTokenForNewUser()
    {
        $email = 'valid@email.com';
        $apiKey = 'correct_key';
        $userId = 1;
        $dbUserData = [
            'id' => $userId,
            'api_key' => hash('sha256', $apiKey)
        ];

        $this->dbManager->shouldReceive('getUserApiKey')
            ->with($email)
            ->once()
            ->andReturn($dbUserData);

        $this->dbManager->shouldReceive('getTokenByUserId')
            ->with($userId)
            ->once()
            ->andReturn(null);

        $this->dbManager->shouldReceive('insertToken')
            ->with($userId, Mockery::pattern('/^[a-f0-9]{20}$/'))
            ->once()
            ->andReturn(true);

        $response = $this->tokenService->genToken($email, $apiKey);

        $this->assertEquals(200, $response['http_code']);
        $this->assertArrayHasKey('token', $response['data']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{20}$/', $response['data']['token']);
    }

    /**
     * @test
     */
    public function updatesTokenForExistingUser()
    {
        $email = 'valid@email.com';
        $apiKey = 'correct_key';
        $userId = 1;
        $dbUserData = [
            'id' => $userId,
            'api_key' => hash('sha256', $apiKey)
        ];
        $oldToken = 'old_token_data';

        $this->dbManager->shouldReceive('getUserApiKey')
            ->with($email)
            ->once()
            ->andReturn($dbUserData);

        $this->dbManager->shouldReceive('getTokenByUserId')
            ->with($userId)
            ->once()
            ->andReturn(['token' => $oldToken]);

        $this->dbManager->shouldReceive('updateToken')
            ->with($userId, Mockery::pattern('/^[a-f0-9]{20}$/'))
            ->once()
            ->andReturn(true);

        $response = $this->tokenService->genToken($email, $apiKey);

        $this->assertEquals(200, $response['http_code']);
        $this->assertNotEquals($oldToken, $response['data']['token']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{20}$/', $response['data']['token']);
    }
}
