<?php

namespace TwitchAnalytics\Validators;

class EnrichedValidator
{
    public function __construct()
    {
    }
    public function isValidLimit($limit): bool
    {
        return $limit >= 1 && $limit <= 100;
    }
}
