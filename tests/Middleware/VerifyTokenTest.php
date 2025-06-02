<?php

namespace TwitchAnalytics\Tests\Middleware;

use \Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Middleware\VerifyToken;
use PHPUnit\Framework\TestCase;

class VerifyTokenTest extends TestCase
{
    /**
     * @test
     */
    public function requestWithoutTokenIsRejected()
    {
        $request = new Request();
        $dbManager = Mockery::mock(MYSQLDBManager::class);
        $tokenVerifier = new VerifyToken($dbManager);
        $response = $tokenVerifier->handle($request, fn () => null);

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
        $dbManager = Mockery::mock(MYSQLDBManager::class);
        $tokenVerifier = new VerifyToken($dbManager);
        $response = $tokenVerifier->handle($request, fn () => null);

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
