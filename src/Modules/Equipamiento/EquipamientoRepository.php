<?php
declare(strict_types=1);

namespace App\Modules\Equipamiento;

use App\Database\Database;
use PDO;

final class EquipamientoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?int $idTanque, ?string $estado): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($idTanque !== null) {
            $where[] = 'e.id_tanque = :id_tanque';
            $params[':id_tanque'] = $idTanque;
        }
        if ($estado !== null && $estado !== '') {
            $where[] = 'e.estado = :estado';
            $params[':estado'] = $estado;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM equipamiento e {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT e.id_equipamiento, e.id_tanque, e.nombre, e.tipo, e.marca, e.modelo,
                   e.fecha_instalacion, e.estado,
                   t.nombre AS tanque_nombre
            FROM equipamiento e
            JOIN tanque t ON t.id_tanque = e.id_tanque
            {$whereSql}
            ORDER BY e.id_equipamiento DESC
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
            SELECT e.*, t.nombre AS tanque_nombre
            FROM equipamiento e
            JOIN tanque t ON t.id_tanque = e.id_tanque
            WHERE e.id_equipamiento = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM equipamiento WHERE id_equipamiento = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO equipamiento
                (id_tanque, nombre, tipo, marca, modelo, fecha_instalacion, estado)
            VALUES
                (:id_tanque, :nombre, :tipo, :marca, :modelo, :fecha_instalacion, :estado)
        ');
        $stmt->execute([
            ':id_tanque'         => $data['id_tanque'],
            ':nombre'            => $data['nombre'],
            ':tipo'              => $data['tipo'] ?? null,
            ':marca'             => $data['marca'] ?? null,
            ':modelo'            => $data['modelo'] ?? null,
            ':fecha_instalacion' => $data['fecha_instalacion'] ?? null,
            ':estado'            => $data['estado'] ?? 'Activo',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE equipamiento SET
                id_tanque         = :id_tanque,
                nombre            = :nombre,
                tipo              = :tipo,
                marca             = :marca,
                modelo            = :modelo,
                fecha_instalacion = :fecha_instalacion,
                estado            = :estado
            WHERE id_equipamiento = :id
        ');
        $stmt->execute([
            ':id_tanque'         => $data['id_tanque'],
            ':nombre'            => $data['nombre'],
            ':tipo'              => $data['tipo'] ?? null,
            ':marca'             => $data['marca'] ?? null,
            ':modelo'            => $data['modelo'] ?? null,
            ':fecha_instalacion' => $data['fecha_instalacion'] ?? null,
            ':estado'            => $data['estado'] ?? 'Activo',
            ':id'                => $id,
        ]);
    }

    public function setEstado(int $id, string $estado): void
    {
        $stmt = $this->db->prepare('UPDATE equipamiento SET estado = :e WHERE id_equipamiento = :id');
        $stmt->execute([':e' => $estado, ':id' => $id]);
    }
}