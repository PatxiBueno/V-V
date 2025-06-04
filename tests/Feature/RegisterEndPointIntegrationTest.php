<?php

namespace TwitchAnalytics\Tests\Feature;

use TwitchAnalytics\Managers\MYSQLDBManager;
use Laravel\Lumen\Testing\TestCase;

class RegisterEndPointIntegrationTest extends TestCase
{
    /**
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../../bootstrap/app.php';
    }
    /**
     * @test
     *
     */
    public function missingMailErrorCode400(): void
    {
        $response = $this->call('POST', 'register', [], [], [], []);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [
                'error' => "The email is mandatory"],
            $responseData
        );
    }
    /**
     * @test
     *
     */
    public function invalidMailIdErrorCode400(): void
    {
        $json = json_encode(['email' => 'motto#gmial.com']);
        $response = $this->call('POST', 'register', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [
                'error' => "The email must be a valid email address"],
            $responseData
        );
    }
    /**
     * @test
     *
     */
    public function rightCredentialGiveApiKey(): void
    {
        $json = json_encode(['email' => 'motto@gmial.com']);
        $mysqlManagerMock = mock(MYSQLDBManager::class);
        $mysqlManagerMock->shouldReceive('getUserByEmail')->once()->with('motto@gmial.com')->andReturn(null);
        $mysqlManagerMock->shouldReceive('insertUserWithHashedApiKey')->once()->andReturn(true);
        $mysqlManagerMock->shouldReceive('updateUserHashedKey')->once()->andReturn(true);

        $this->app->instance(MYSQLDBManager::class, $mysqlManagerMock);

        $response = $this->call('POST', 'register', [], [], [], [], $json);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
