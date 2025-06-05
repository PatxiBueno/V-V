<?php

namespace TwitchAnalytics\Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Validators\EnrichedValidator;

class EnrichedValidatorTest extends TestCase
{
    private EnrichedValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new EnrichedValidator();
    }

    /**
     * @test
     **/
    public function validLimitProvidedReturnsTrue()
    {
        $this->assertFalse($this->validator->isValidLimit(-4));
    }

    /**
     * @test
     **/
    public function invalidLimitProvidedReturnsFalse()
    {
        $this->assertTrue($this->validator->isValidLimit(3));
    }

}
