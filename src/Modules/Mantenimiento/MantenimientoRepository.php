<?php
declare(strict_types=1);

namespace App\Modules\Mantenimiento;

use App\Database\Database;
use PDO;

final class MantenimientoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?int $idTanque, ?int $idTipo, ?int $idEmpleado, ?string $desde, ?string $hasta): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($idTanque !== null) {
            $where[] = 'm.id_tanque = :id_tanque';
            $params[':id_tanque'] = $idTanque;
        }
        if ($idTipo !== null) {
            $where[] = 'm.id_tipo_mantenimiento = :id_tipo';
            $params[':id_tipo'] = $idTipo;
        }
        if ($idEmpleado !== null) {
            $where[] = 'm.id_empleado = :id_empleado';
            $params[':id_empleado'] = $idEmpleado;
        }
        if ($desde !== null) {
            $where[] = 'm.fecha >= :desde';
            $params[':desde'] = $desde . ' 00:00:00';
        }
        if ($hasta !== null) {
            $where[] = 'm.fecha <= :hasta';
            $params[':hasta'] = $hasta . ' 23:59:59';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM mantenimiento m {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT m.id_mantenimiento, m.id_tanque, m.id_tipo_mantenimiento, m.id_empleado,
                   m.fecha, m.observaciones,
                   t.nombre AS tanque_nombre,
                   tm.nombre AS tipo_nombre,
                   CONCAT(e.nombre, ' ', e.apellido_paterno) AS empleado_nombre
            FROM mantenimiento m
            JOIN tanque t ON t.id_tanque = m.id_tanque
            JOIN tipo_mantenimiento tm ON tm.id_tipo_mantenimiento = m.id_tipo_mantenimiento
            JOIN empleado e ON e.id_empleado = m.id_empleado
            {$whereSql}
            ORDER BY m.fecha DESC
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
            SELECT m.*, t.nombre AS tanque_nombre,
                   tm.nombre AS tipo_nombre,
                   CONCAT(e.nombre, " ", e.apellido_paterno) AS empleado_nombre
            FROM mantenimiento m
            JOIN tanque t ON t.id_tanque = m.id_tanque
            JOIN tipo_mantenimiento tm ON tm.id_tipo_mantenimiento = m.id_tipo_mantenimiento
            JOIN empleado e ON e.id_empleado = m.id_empleado
            WHERE m.id_mantenimiento = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM mantenimiento WHERE id_mantenimiento = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO mantenimiento
                (id_tanque, id_tipo_mantenimiento, id_empleado, fecha, observaciones)
            VALUES
                (:id_tanque, :id_tipo, :id_empleado, :fecha, :observaciones)
        ');
        $stmt->execute([
            ':id_tanque'      => $data['id_tanque'],
            ':id_tipo'        => $data['id_tipo_mantenimiento'],
            ':id_empleado'    => $data['id_empleado'],
            ':fecha'          => $data['fecha'] ?? date('Y-m-d H:i:s'),
            ':observaciones'  => $data['observaciones'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE mantenimiento SET
                id_tanque             = :id_tanque,
                id_tipo_mantenimiento = :id_tipo,
                id_empleado           = :id_empleado,
                fecha                 = :fecha,
                observaciones         = :observaciones
            WHERE id_mantenimiento = :id
        ');
        $stmt->execute([
            ':id_tanque'      => $data['id_tanque'],
            ':id_tipo'        => $data['id_tipo_mantenimiento'],
            ':id_empleado'    => $data['id_empleado'],
            ':fecha'          => $data['fecha'] ?? date('Y-m-d H:i:s'),
            ':observaciones'  => $data['observaciones'] ?? null,
            ':id'             => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM mantenimiento WHERE id_mantenimiento = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Devuelve la última fecha por tanque+tipo (para alertas de periodicidad).
     */
    public function ultimosPorTanqueTipo(): array
    {
        $stmt = $this->db->query('
            SELECT m.id_tanque, m.id_tipo_mantenimiento, MAX(m.fecha) AS ultima_fecha
            FROM mantenimiento m
            GROUP BY m.id_tanque, m.id_tipo_mantenimiento
        ');
        return $stmt->fetchAll();
    }
}