<?php

namespace TwitchAnalytics;

class ResponseTwitchData
{
    private int $httpResponseCode;

    private string $httpResponseUserData;

    public function __construct($httpResponseCode, $httpResponseUserData)
    {
        $this->httpResponseCode = $httpResponseCode;
        $this->httpResponseUserData = $httpResponseUserData;
    }

    public function getHttpResponseCode(): int
    {
        return $this->httpResponseCode;
    }

    public function getHttpResponseUserData(): string
    {
        return $this->httpResponseUserData;
    }
}
