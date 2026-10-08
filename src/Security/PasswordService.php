<?php
declare(strict_types=1);

namespace App\Security;

use App\Config\Config;

final class PasswordService
{
    public static function hash(string $plain): string
    {
        if ($plain === '') {
            throw new \InvalidArgumentException('Password vacío');
        }

        $hash = password_hash($plain, PASSWORD_BCRYPT, [
            'cost' => (int) Config::get('security.bcrypt_rounds', 12),
        ]);

        if ($hash === false) {
            throw new \RuntimeException('No se pudo generar el hash');
        }
        return $hash;
    }

    public static function verify(string $plain, string $hash): bool
    {
        if ($plain === '' || $hash === '') return false;
        return password_verify($plain, $hash);
    }
}