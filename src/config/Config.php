<?php
declare(strict_types=1);

namespace App\Config;

use Dotenv\Dotenv;

final class Config
{
    private static bool $loaded = false;
    private static array $values = [];

    public static function load(string $rootPath): void
    {
        if (self::$loaded) return;

        if (file_exists($rootPath . '/.env')) {
            Dotenv::createImmutable($rootPath)->safeLoad();
        }

        self::$values = [
            'app_env' => $_ENV['APP_ENV'] ?? 'development',

            'jwt' => [
                'secret'          => $_ENV['JWT_SECRET'] ?? 'dev-secret',
                'refresh_secret'  => $_ENV['JWT_REFRESH_SECRET'] ?? 'dev-refresh-secret',
                'expires_in'      => (int) ($_ENV['JWT_EXPIRES_IN'] ?? 3600),
                'refresh_expires' => (int) ($_ENV['JWT_REFRESH_EXPIRES_IN'] ?? 604800),
                'issuer'          => $_ENV['JWT_ISSUER'] ?? 'acuario-tiburon-feliz',
                'audience'        => $_ENV['JWT_AUDIENCE'] ?? 'pwa',
            ],

            'security' => [
                'bcrypt_rounds' => (int) ($_ENV['BCRYPT_ROUNDS'] ?? 12),
            ],
        ];

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$values;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}