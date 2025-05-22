<?php

namespace TwitchAnalytics;

class ResponseTwitchData
{
    private int $httpResponseCode;

    private string $httpResponseData;

    public function __construct($httpResponseCode, $httpResponseData)
    {
        $this->httpResponseCode = $httpResponseCode;
        $this->httpResponseData = $httpResponseData;
    }

    public function getHttpResponseCode(): int
    {
        return $this->httpResponseCode;
    }

    public function getHttpResponseData(): string
    {
        return $this->httpResponseData;
    }
}

