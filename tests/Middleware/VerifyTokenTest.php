<?php

namespace TwitchAnalytics\Tests\Middleware;

use \Illuminate\Http\Request;
use Mockery;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Middleware\VerifyToken;
use PHPUnit\Framework\TestCase;

class VerifyTokenTest extends TestCase
{
    private $dbManager;
    private $tokenVerifyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbManager = Mockery::mock(MYSQLDBManager::class);
        $this->tokenVerifyer = new VerifyToken($this->dbManager);
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
            json_encode(['error' => 'Unauthorized. Token is invalid or expired.']),
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
            json_encode(['error' => 'Unauthorized. Token is invalid or expired.']),
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
            json_encode(['error' => 'Unauthorized. Token is invalid or expired.']),
            $response->getContent()
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
