<?php

namespace TwitchAnalytics\Tests\Feature;

use Laravel\Lumen\Testing\TestCase;
use TwitchAnalytics\Controllers\TokenController;
use TwitchAnalytics\Managers\MYSQLDBManager;
use TwitchAnalytics\Managers\TwitchAPIManager;
use TwitchAnalytics\Middleware\TokenVerifyer;
use TwitchAnalytics\ResponseTwitchData;

class TokenEndPointIntegration extends TestCase
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
     **/
    public function noApiKeyProvidedErrorCode400()
    {
        $json = json_encode(['email' => 'motto@gmial.com']);
        $response = $this->call('POST', 'token', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            "error" => "The api_key is mandatory",
        ], $responseData);
    }

    /**
     * @test
     **/
    public function noEmailProvidedErrorCode400()
    {
        $json = json_encode(['api_key' => 'apikiprovided']);
        $response = $this->call('POST', 'token', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            "error" => "The email is mandatory",
        ], $responseData);
    }

    /**
     * @test
     **/
    public function invalidEmailProvidedErrorCode400()
    {
        $json = json_encode(['email' => 'motto#gmial.com', 'api_key' => 'apikiprovided']);
        $response = $this->call('POST', 'token', [], [], [], [], $json);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals([
            "error" => "The email must be a valid email address",
        ], $responseData);
    }


}
