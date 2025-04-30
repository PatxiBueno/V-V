<?php

declare(strict_types=1);

namespace TwitchAnalytics\Controllers\GetUserPlatformAge;

class UserNameValidator
{
    private const MAX_LENGTH = 25;
    private const MIN_LENGTH = 3;

    public function validate(?string $name): string
    {
        if (!isset($name)) {
            throw new ValidationException('Name parameter is required');
        }

        $sanitizedName = strip_tags($name);
        $sanitizedName = htmlspecialchars($sanitizedName, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedName)) {
            throw new ValidationException('Name parameter is required');
        }

        if (strlen($sanitizedName) < self::MIN_LENGTH) {
            throw new ValidationException('Name must be at least 3 characters long');
        }

        if (strlen($sanitizedName) > self::MAX_LENGTH) {
            throw new ValidationException('Name cannot be longer than 25 characters');
        }

        return $sanitizedName;
    }
}
