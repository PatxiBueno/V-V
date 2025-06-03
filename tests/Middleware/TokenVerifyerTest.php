<?php

namespace TwitchAnalytics\Tests\Middleware;

use \Illuminate\Http\Request;
use Mockery;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Middleware\TokenVerifyer;
use PHPUnit\Framework\TestCase;

class TokenVerifyerTest extends TestCase
{
    private $dbManager;
    private $tokenVerifyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbManager = Mockery::mock(MYSQLDBManager::class);
        $this->tokenVerifyer = new TokenVerifyer($this->dbManager);
    }
    /**
     * @test
     */
    public function requestWithoutTokenIsRejected()
    {
        $request = new Request();
        $response = $this->tokenVerifyer->handle($request, fn () => null);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(
            json_encode(['error' => 'Unauthorized. TokenService is invalid or expired.']),
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function requestWithInvalidTokenFormatIsRejected()
    {
        $request = new Request();
        $request->headers->set('Authorization', 'InvalidTokenFormat token');

        $response = $this->tokenVerifyer->handle($request, fn () => null);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(
            json_encode(['error' => 'Unauthorized. TokenService is invalid or expired.']),
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function requestWithNonExistentTokenIsRejected()
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer invalid_token');
        $this->dbManager->shouldReceive('getExpirationDayOfToken')
            ->with('invalid_token')
            ->andReturn(null);

        $response = $this->tokenVerifyer->handle($request, fn () => null);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(
            json_encode(['error' => 'Unauthorized. TokenService is invalid or expired.']),
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function requestWithExpiredTokenIsRejected()
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer expired_token');
        $invalidDate = date('Y-m-d H:i:s', time() - (4 * 24 * 3600));
        $this->dbManager->shouldReceive('getExpirationDayOfToken')
            ->with('expired_token')
            ->andReturn(["fecha_token" => $invalidDate]);

        $response = $this->tokenVerifyer->handle($request, fn () => null);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(
            json_encode(['error' => 'Unauthorized. TokenService is invalid or expired.']),
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function requestWithValidTokenIsAccepted()
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer valid_token');
        $validDate = date('Y-m-d H:i:s', time() - (2 * 24 * 3600));
        $this->dbManager->shouldReceive('getExpirationDayOfToken')
            ->with('valid_token')
            ->andReturn(["fecha_token" => $validDate]);

        $called = false;
        $this->tokenVerifyer->handle($request, function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($called);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
