<?php

namespace App\helpers;

class Uri
{
    public static function get(string $type): string
    {
        $parts = parse_url($_SERVER['REQUEST_URI'] ?? '/');
        if (!is_array($parts)) {
            return $type === 'path' ? '/' : '';
        }

        if ($type === 'path') {
            return self::normalizePath($parts['path'] ?? '/');
        }

        return (string) ($parts[$type] ?? '');
    }

    public static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        if ($path === '//' || $path === '') {
            return '/';
        }

        return $path;
    }
}
