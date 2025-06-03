<?php

namespace TwitchAnalytics\Validators;

class UserValidator
{
    public function __construct()
    {
    }
    public function isValidId($userId): bool
    {
        return ($userId !== null && $userId >= 0 && $userId < 20800);
    }
}
