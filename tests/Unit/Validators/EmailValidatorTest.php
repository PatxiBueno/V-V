<?php

namespace TwitchAnalytics\Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Validators\EmailValidator;

class EmailValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function invalidEmailIsNotValidated(): void
    {
        $emailValidator = new EmailValidator();
        $data = ["email" => "invalid-email"];
        $this->assertFalse($emailValidator->emailIsValid($data["email"]));
    }

    /**
     * @test
     */
    public function validEmailIsValidated(): void
    {
        $emailValidator = new EmailValidator();
        $data = ["email" => "valid@gmail.com"];
        $this->assertTrue($emailValidator->emailIsValid($data["email"]));
    }
}
