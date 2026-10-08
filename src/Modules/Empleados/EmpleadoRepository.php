<?php
declare(strict_types=1);

namespace App\Modules\Empleados;

use App\Database\Database;
use PDO;

final class EmpleadoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?bool $activo, ?string $q): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($activo !== null) {
            $where[] = 'activo = :activo';
            $params[':activo'] = $activo ? 1 : 0;
        }
        if ($q !== null && $q !== '') {
            $where[] = '(nombre LIKE :q OR apellido_paterno LIKE :q OR apellido_materno LIKE :q OR correo LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM empleado {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT id_empleado, nombre, apellido_paterno, apellido_materno,
                   telefono, correo, puesto, fecha_ingreso, activo
            FROM empleado
            {$whereSql}
            ORDER BY id_empleado DESC
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
            SELECT id_empleado, nombre, apellido_paterno, apellido_materno,
                   telefono, correo, puesto, fecha_ingreso, activo
            FROM empleado WHERE id_empleado = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO empleado
                (nombre, apellido_paterno, apellido_materno, telefono, correo, puesto, fecha_ingreso, activo)
            VALUES
                (:nombre, :ap, :am, :telefono, :correo, :puesto, :fecha_ingreso, :activo)
        ');
        $stmt->execute([
            ':nombre'        => $data['nombre'],
            ':ap'            => $data['apellido_paterno'],
            ':am'            => $data['apellido_materno'] ?? null,
            ':telefono'      => $data['telefono'] ?? null,
            ':correo'        => $data['correo'] ?? null,
            ':puesto'        => $data['puesto'],
            ':fecha_ingreso' => $data['fecha_ingreso'] ?? null,
            ':activo'        => $data['activo'] ?? true ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE empleado SET
                nombre           = :nombre,
                apellido_paterno = :ap,
                apellido_materno = :am,
                telefono         = :telefono,
                correo           = :correo,
                puesto           = :puesto,
                fecha_ingreso    = :fecha_ingreso,
                activo           = :activo
            WHERE id_empleado = :id
        ');
        $stmt->execute([
            ':nombre'        => $data['nombre'],
            ':ap'            => $data['apellido_paterno'],
            ':am'            => $data['apellido_materno'] ?? null,
            ':telefono'      => $data['telefono'] ?? null,
            ':correo'        => $data['correo'] ?? null,
            ':puesto'        => $data['puesto'],
            ':fecha_ingreso' => $data['fecha_ingreso'] ?? null,
            ':activo'        => $data['activo'] ?? true ? 1 : 0,
            ':id'            => $id,
        ]);
    }

    public function setActivo(int $id, bool $activo): void
    {
        $stmt = $this->db->prepare('UPDATE empleado SET activo = :a WHERE id_empleado = :id');
        $stmt->execute([':a' => $activo ? 1 : 0, ':id' => $id]);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM empleado WHERE id_empleado = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}