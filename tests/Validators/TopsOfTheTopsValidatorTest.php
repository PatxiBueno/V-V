<?php

namespace TwitchAnalytics\Tests\Validators;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Validators\TopsOfTheTopsValidator;

class TopsOfTheTopsValidatorTest extends TestCase
{
    private TopsOfTheTopsValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new TopsOfTheTopsValidator();
    }
    /**
     * @test
     **/
    public function validLimitProvidedReturnsTrue()
    {
        $this->assertFalse($this->validator->validateSince(-4));
    }
    /**
     * @test
     **/
    public function invalidLimitProvidedReturnsFalse()
    {
        $this->assertTrue($this->validator->validateSince(3));
    }
}
