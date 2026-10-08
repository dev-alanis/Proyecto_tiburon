<?php
declare(strict_types=1);

namespace App\Security;

use App\Security\Exceptions\ForbiddenException;
use App\Security\Exceptions\UnauthorizedException;

final class AuthMiddleware
{
    /** Devuelve [id_usuario, rol, id_empleado, nombre_usuario] o lanza excepción. */
    public static function authenticate(): array
    {
        $token = self::extractBearer();
        if ($token === null) {
            throw new UnauthorizedException('Token no proporcionado');
        }

        $payload = JwtService::verifyAccessToken($token);

        return [
            'id_usuario'     => (int) $payload['sub'],
            'rol'            => $payload['rol'],
            'id_empleado'    => (int) $payload['id_empleado'],
            'nombre_usuario' => $payload['nombre_usuario'],
        ];
    }

    /** Autoriza roles. Debe llamarse DESPUÉS de authenticate(). */
    public static function authorize(array $user, string ...$rolesPermitidos): void
    {
        if (empty($rolesPermitidos)) return;
        if (!in_array($user['rol'], $rolesPermitidos, true)) {
            throw new ForbiddenException('No tienes permisos para esta acción');
        }
    }

    /** Devuelve user o null sin lanzar excepción. */
    public static function optionalAuth(): ?array
    {
        try {
            return self::authenticate();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function extractBearer(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!str_starts_with($header, 'Bearer ')) return null;
        $token = trim(substr($header, 7));
        return $token !== '' ? $token : null;
    }
}