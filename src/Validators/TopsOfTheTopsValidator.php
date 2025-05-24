<?php

namespace TwitchAnalytics\Validators;

class TopsOfTheTopsValidator
{
    public function __construct()
    {
    }
    public function validateSince($since): bool
    {
        return ($since >= 0);
    }
}
