<?php

namespace TwitchAnalytics\Validators;

class ApiKeyValidator
{
    public function __construct()
    {
    }
    public function existsApiKey($data): bool
    {
        return isset($data['api_key']);
    }
}
