<?php

namespace TwitchAnalytics\Validators;

class EmailValidator
{
    public function __construct()
    {
    }
    public function existsEmail($data): bool
    {
        return isset($data['email']);
    }
    public function emailIsValid($email): bool
    {
        return filter_var(filter_var($email, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
    }
}