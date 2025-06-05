<?php

namespace TwitchAnalytics\Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Validators\ApiKeyValidator;

class ApiKeyValidatorTest extends TestCase
{
    private ApiKeyValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ApiKeyValidator();
    }

    /**
     * @test
     **/
    public function noApiKeyProvidedReturnsFalse()
    {
        $json = ['no_api_key' => 'non_existing_api_key'];

        $this->assertFalse($this->validator->existsApiKey($json));
    }

    /**
     * @test
     **/
    public function apiKeyProvidedReturnsTrue()
    {
        $json = ['api_key' => 'apikiprovided'];

        $this->assertTrue($this->validator->existsApiKey($json));
    }
}
