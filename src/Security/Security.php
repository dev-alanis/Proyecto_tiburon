<?php
declare(strict_types=1);

namespace App\Security;

final class Security
{
    // Roles
    public static function roles(): array { return Roles::ALL; }

    // Auth
    public static function authenticate(): array { return AuthMiddleware::authenticate(); }
    public static function authorize(array $user, string ...$roles): void
    {
        AuthMiddleware::authorize($user, ...$roles);
    }
    public static function optionalAuth(): ?array { return AuthMiddleware::optionalAuth(); }

    // JWT
    public static function buildTokenPair(array $usuario): array
    {
        return JwtService::buildTokenPair($usuario);
    }
    public static function verifyRefreshToken(string $token): array
    {
        return JwtService::verifyRefreshToken($token);
    }

    // Password
    public static function hashPassword(string $plain): string
    {
        return PasswordService::hash($plain);
    }
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return PasswordService::verify($plain, $hash);
    }
}