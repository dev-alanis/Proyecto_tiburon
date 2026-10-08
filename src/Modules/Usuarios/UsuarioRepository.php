<?php
declare(strict_types=1);

namespace App\Modules\Usuarios;

use App\Database\Database;
use PDO;

final class UsuarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?bool $activo, ?string $rol): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($activo !== null) {
            $where[] = 'u.activo = :activo';
            $params[':activo'] = $activo ? 1 : 0;
        }
        if ($rol !== null && $rol !== '') {
            $where[] = 'u.rol = :rol';
            $params[':rol'] = $rol;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM usuario u {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT u.id_usuario, u.id_empleado, u.nombre_usuario, u.rol, u.activo, u.fecha_creacion,
                   CONCAT(e.nombre, ' ', e.apellido_paterno) AS nombre_empleado
            FROM usuario u
            JOIN empleado e ON e.id_empleado = u.id_empleado
            {$whereSql}
            ORDER BY u.id_usuario DESC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.id_usuario, u.id_empleado, u.nombre_usuario, u.rol, u.activo, u.fecha_creacion,
                   CONCAT(e.nombre, " ", e.apellido_paterno) AS nombre_empleado
            FROM usuario u
            JOIN empleado e ON e.id_empleado = u.id_empleado
            WHERE u.id_usuario = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByNombreUsuario(string $nombreUsuario): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuario WHERE nombre_usuario = :nu LIMIT 1');
        $stmt->execute([':nu' => $nombreUsuario]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existsEmpleadoVinculado(int $idEmpleado): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM usuario WHERE id_empleado = :id');
        $stmt->execute([':id' => $idEmpleado]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO usuario (id_empleado, nombre_usuario, password_hash, rol, activo)
            VALUES (:id_empleado, :nombre_usuario, :password_hash, :rol, :activo)
        ');
        $stmt->execute([
            ':id_empleado'    => $data['id_empleado'],
            ':nombre_usuario' => $data['nombre_usuario'],
            ':password_hash'  => $data['password_hash'],
            ':rol'            => $data['rol'],
            ':activo'         => $data['activo'] ?? true ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        // Si viene password, se actualiza; si no, se conserva.
        $sql = '
            UPDATE usuario SET
                id_empleado    = :id_empleado,
                nombre_usuario = :nombre_usuario,
                rol            = :rol,
                activo         = :activo
        ';
        $params = [
            ':id_empleado'    => $data['id_empleado'],
            ':nombre_usuario' => $data['nombre_usuario'],
            ':rol'            => $data['rol'],
            ':activo'         => $data['activo'] ?? true ? 1 : 0,
            ':id'             => $id,
        ];

        if (!empty($data['password_hash'])) {
            $sql .= ', password_hash = :password_hash';
            $params[':password_hash'] = $data['password_hash'];
        }

        $sql .= ' WHERE id_usuario = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function setActivo(int $id, bool $activo): void
    {
        $stmt = $this->db->prepare('UPDATE usuario SET activo = :a WHERE id_usuario = :id');
        $stmt->execute([':a' => $activo ? 1 : 0, ':id' => $id]);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM usuario WHERE id_usuario = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}