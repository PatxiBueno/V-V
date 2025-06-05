<?php

namespace TwitchAnalytics\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Service\RegisterService;

use function PHPUnit\Framework\assertEquals;

class RegisterServiceTest extends TestCase
{
    /**
     * @test
     *
     */
    public function registerHappyPath(): void
    {
        $mysqlManagerMock = mock(MYSQLDBManager::class);
        $mysqlManagerMock->shouldReceive('getUserByEmail')->once()->with('motto@gmial.com')->andReturn(null);
        $mysqlManagerMock->shouldReceive('insertUserWithHashedApiKey')->once()->andReturn(true);
        $mysqlManagerMock->shouldReceive('updateUserHashedKey')->once()->andReturn(true);

        $registerService = new RegisterService($mysqlManagerMock);
        $response = $registerService->registerUser("motto@gmial.com");

        assertEquals(200, $response["http_code"]);
        $this->assertArrayHasKey('api_key', $response["data"]);
    }
}
