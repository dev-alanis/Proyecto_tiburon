<?php
declare(strict_types=1);

namespace App\Security;

final class Roles
{
    public const ADMIN    = 'ADMIN';
    public const CUIDADOR = 'CUIDADOR';

    public const ALL = [self::ADMIN, self::CUIDADOR];

    public static function isValid(string $rol): bool
    {
        return in_array($rol, self::ALL, true);
    }
}