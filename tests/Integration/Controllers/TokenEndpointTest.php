<?php

declare(strict_types=1);

namespace TwitchAnalytics\Tests\Integration\Controllers;

use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\ResponseTwitchData;
use TwitchAnalytics\Service\Token;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Request;

class TokenEndPointTest extends TestCase
{
    /**
     * @test
     *
     */
    public function missingMailIdErrorCode400(): void
    {
        $request = new Request();
        $mysqlManager = mock(MYSQLDBManager::class);
        $token = new Token($request, $mysqlManager);

        $response = $token->genToken();
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
        $body = fopen('php://memory', 'r+');
        fwrite($body, $json);
        rewind($body);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $body);

        $mysqlManager = mock(MYSQLDBManager::class);
        $token = new Token($request, $mysqlManager);

        $response = $token->genToken();
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
    public function missingAPIkeyErrorCode400(): void
    {
        $json = json_encode(['email' => 'motto@gmial.com']);
        $body = fopen('php://memory', 'r+');
        fwrite($body, $json);
        rewind($body);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $body);

        $mysqlManager = mock(MYSQLDBManager::class);
        $token = new Token($request, $mysqlManager);

        $response = $token->genToken();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [
            'error' => "The api_key is mandatory"],
            $responseData
        );
    }

    /**
     * @test
     *
     */
    public function nonRegisteredEmailErrorCode400(): void
    {
        $json = json_encode(['email' => 'motto@gmial.com','api_key' => 'apikimokery']);
        $body = fopen('php://memory', 'r+');
        fwrite($body, $json);
        rewind($body);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/token',
            'CONTENT_TYPE'   => 'application/json',
        ];
        $request = new Request([], [], [], [], [], $server, $body);

        $mysqlManager = mock(MYSQLDBManager::class);
        $mysqlManager->shouldReceive('getUserApiKey')->andReturn(null);
        $token = new Token($request, $mysqlManager);

        $response = $token->genToken();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            [
            'error' => "The email must be a valid email address"],
            $responseData
        );
    }
}
