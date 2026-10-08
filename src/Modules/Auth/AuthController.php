<?php
declare(strict_types=1);

namespace App\Modules\Auth;

use App\Database\Database;
use App\Http\Request;
use App\Http\Response;
use App\Security\Security;
use PDO;

final class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function login(): void
    {
        $body = Request::body();
        $nombreUsuario = trim((string) ($body['nombre_usuario'] ?? ''));
        $password      = (string) ($body['password'] ?? '');

        if ($nombreUsuario === '' || $password === '') {
            Response::error('VALIDATION', 'Nombre de usuario y contraseña son obligatorios', 422);
            return;
        }

        $stmt = $this->db->prepare('
            SELECT u.id_usuario, u.id_empleado, u.nombre_usuario,
                   u.password_hash, u.rol, u.activo,
                   CONCAT(e.nombre, " ", e.apellido_paterno) AS nombre_completo
            FROM usuario u
            JOIN empleado e ON e.id_empleado = u.id_empleado
            WHERE u.nombre_usuario = :nu
            LIMIT 1
        ');
        $stmt->execute([':nu' => $nombreUsuario]);
        $usuario = $stmt->fetch();

        if (!$usuario || !$usuario['activo']
            || !Security::verifyPassword($password, $usuario['password_hash'])) {
            Response::error('UNAUTHORIZED', 'Credenciales inválidas', 401);
            return;
        }

        $tokens = Security::buildTokenPair([
            'id_usuario'     => $usuario['id_usuario'],
            'rol'            => $usuario['rol'],
            'id_empleado'    => $usuario['id_empleado'],
            'nombre_usuario' => $usuario['nombre_usuario'],
        ]);

        Response::json([
            'token'        => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
            'usuario'      => [
                'id_usuario'      => (int) $usuario['id_usuario'],
                'id_empleado'     => (int) $usuario['id_empleado'],
                'nombre_usuario'  => $usuario['nombre_usuario'],
                'rol'             => $usuario['rol'],
                'nombre_completo' => $usuario['nombre_completo'],
            ],
        ]);
    }

    public function refresh(): void
    {
        $body = Request::body();
        $refreshToken = (string) ($body['refreshToken'] ?? '');

        if ($refreshToken === '') {
            Response::error('VALIDATION', 'refreshToken es obligatorio', 422);
            return;
        }

        try {
            $payload = Security::verifyRefreshToken($refreshToken);
        } catch (\Throwable $e) {
            Response::error('UNAUTHORIZED', 'Refresh token inválido o expirado', 401);
            return;
        }

        $idUsuario = (int) $payload['sub'];

        $stmt = $this->db->prepare('
            SELECT u.id_usuario, u.id_empleado, u.nombre_usuario, u.rol, u.activo
            FROM usuario u
            WHERE u.id_usuario = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $idUsuario]);
        $usuario = $stmt->fetch();

        if (!$usuario || !$usuario['activo']) {
            Response::error('UNAUTHORIZED', 'Usuario inactivo o inexistente', 401);
            return;
        }

        $tokens = Security::buildTokenPair([
            'id_usuario'     => $usuario['id_usuario'],
            'rol'            => $usuario['rol'],
            'id_empleado'    => $usuario['id_empleado'],
            'nombre_usuario' => $usuario['nombre_usuario'],
        ]);

        Response::json([
            'token'        => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
        ]);
    }

    public function logout(): void
    {
        // Con JWT stateless, el logout real lo hace el cliente descartando el token.
        // Este endpoint existe para simetría y para futuras blacklists.
        $user = Security::authenticate();
        Response::json(['data' => ['mensaje' => 'Sesión cerrada', 'usuario' => $user['nombre_usuario']]]);
    }

    public function me(): void
    {
        $user = Security::authenticate();

        $stmt = $this->db->prepare('
            SELECT u.id_usuario, u.id_empleado, u.nombre_usuario, u.rol, u.activo, u.fecha_creacion,
                   CONCAT(e.nombre, " ", e.apellido_paterno, " ", COALESCE(e.apellido_materno, "")) AS nombre_completo,
                   e.correo, e.puesto
            FROM usuario u
            JOIN empleado e ON e.id_empleado = u.id_empleado
            WHERE u.id_usuario = :id
        ');
        $stmt->execute([':id' => $user['id_usuario']]);
        $row = $stmt->fetch();

        if (!$row) {
            Response::error('NOT_FOUND', 'Usuario no encontrado', 404);
            return;
        }
        Response::json(['data' => $row]);
    }
}