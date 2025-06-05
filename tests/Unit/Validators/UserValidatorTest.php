<?php

namespace TwitchAnalytics\Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Validators\UserValidator;

class UserValidatorTest extends TestCase
{
    private UserValidator $userValidator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->userValidator = new UserValidator();
    }
    /**
     * @test
     **/
    public function nullUserIdProvidedReturnsFalse()
    {
        $this->assertFalse($this->userValidator->isValidId(null));
    }
    /**
     * @test
     **/
    public function negativeUserIdProvidedReturnsFalse()
    {
        $this->assertFalse($this->userValidator->isValidId(-2));
    }
    /**
     * @test
     **/
    public function tooLargeUserIdProvidedReturnsFalse()
    {
        $this->assertFalse($this->userValidator->isValidId(20800));
    }
    /**
     * @test
     **/
    public function validUserIdProvidedReturnsTrue()
    {
        $this->assertTrue($this->userValidator->isValidId(73));
    }
}
