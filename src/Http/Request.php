<?php
declare(strict_types=1);

namespace App\Http;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($uri, '?');
        if ($pos !== false) $uri = substr($uri, 0, $pos);

        // Quita la base (/5to_proyecto_tuburon/public)
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        return $uri === '' ? '/' : $uri;
    }

    public static function body(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        if ($raw === '') return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function queryInt(string $key, int $default = 0): int
    {
        return isset($_GET[$key]) ? (int) $_GET[$key] : $default;
    }

    public static function queryBool(string $key): ?bool
    {
        if (!isset($_GET[$key])) return null;
        return filter_var($_GET[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}