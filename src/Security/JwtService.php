<?php
declare(strict_types=1);

namespace App\Security;

use App\Config\Config;
use App\Security\Exceptions\TokenExpiredException;
use App\Security\Exceptions\UnauthorizedException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

final class JwtService
{
    /** Genera par de tokens para un usuario de la BD. */
    public static function buildTokenPair(array $usuario): array
    {
        $accessPayload = [
            'iss'            => Config::get('jwt.issuer'),
            'aud'            => Config::get('jwt.audience'),
            'iat'            => time(),
            'exp'            => time() + (int) Config::get('jwt.expires_in'),
            'sub'            => (int) $usuario['id_usuario'],
            'rol'            => $usuario['rol'],
            'id_empleado'    => (int) $usuario['id_empleado'],
            'nombre_usuario' => $usuario['nombre_usuario'],
        ];

        $refreshPayload = [
            'iss' => Config::get('jwt.issuer'),
            'aud' => Config::get('jwt.audience'),
            'iat' => time(),
            'exp' => time() + (int) Config::get('jwt.refresh_expires'),
            'sub' => (int) $usuario['id_usuario'],
        ];

        return [
            'token'        => JWT::encode($accessPayload, Config::get('jwt.secret'), 'HS256'),
            'refreshToken' => JWT::encode($refreshPayload, Config::get('jwt.refresh_secret'), 'HS256'),
        ];
    }

    public static function verifyAccessToken(string $token): array
    {
        return self::decode($token, Config::get('jwt.secret'));
    }

    public static function verifyRefreshToken(string $token): array
    {
        return self::decode($token, Config::get('jwt.refresh_secret'));
    }

    private static function decode(string $token, string $secret): array
    {
        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            throw new TokenExpiredException();
        } catch (SignatureInvalidException $e) {
            throw new UnauthorizedException('Token inválido');
        } catch (\Throwable $e) {
            throw new UnauthorizedException('Token inválido');
        }
    }
}