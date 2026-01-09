<?php

namespace App\Service;

class RequestDataExtractor
{
    public static function getString(array $requestData, string $key): ?string
    {
        return is_string($requestData[$key] ?? null) ? $requestData[$key] : null;
    }

    public static function getArray(array $requestData, string $key): array
    {
        return is_array($requestData[$key] ?? null) ? $requestData[$key] : [];
    }
}
