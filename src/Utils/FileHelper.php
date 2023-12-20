<?php

namespace App\Utils;

class FileHelper
{
    public static function fileSizeFormatter($bytes, $precision = 2): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, \count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision).' '.$units[$pow];
    }
}
