<?php
declare(strict_types=1);

namespace App\Modules\Mantenimiento;

use App\Database\Database;
use PDO;

final class TipoMantenimientoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query('
            SELECT id_tipo_mantenimiento, nombre, descripcion, periodicidad_dias
            FROM tipo_mantenimiento ORDER BY nombre ASC
        ');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tipo_mantenimiento WHERE id_tipo_mantenimiento = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByNombre(string $nombre): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tipo_mantenimiento WHERE nombre = :n LIMIT 1');
        $stmt->execute([':n' => $nombre]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM tipo_mantenimiento WHERE id_tipo_mantenimiento = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO tipo_mantenimiento (nombre, descripcion, periodicidad_dias)
            VALUES (:nombre, :descripcion, :periodicidad)
        ');
        $stmt->execute([
            ':nombre'       => $data['nombre'],
            ':descripcion'  => $data['descripcion'] ?? null,
            ':periodicidad' => $data['periodicidad_dias'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE tipo_mantenimiento
            SET nombre = :nombre, descripcion = :descripcion, periodicidad_dias = :periodicidad
            WHERE id_tipo_mantenimiento = :id
        ');
        $stmt->execute([
            ':nombre'       => $data['nombre'],
            ':descripcion'  => $data['descripcion'] ?? null,
            ':periodicidad' => $data['periodicidad_dias'] ?? null,
            ':id'           => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM tipo_mantenimiento WHERE id_tipo_mantenimiento = :id');
        $stmt->execute([':id' => $id]);
    }

    public function tieneMantenimientos(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM mantenimiento WHERE id_tipo_mantenimiento = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}