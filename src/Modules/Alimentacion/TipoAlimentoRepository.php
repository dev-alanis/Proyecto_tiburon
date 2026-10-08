<?php
declare(strict_types=1);

namespace App\Modules\Alimentacion;

use App\Database\Database;
use PDO;

/**
 * ============================================================
 *  TipoAlimentoRepository — Catálogo de tipos de alimento
 * ============================================================
 *  Relación: cada tipo_alimento puede (o no) apuntar a un
 *  articulo del inventario. Cuando apunta, la alimentación
 *  descuenta stock desde ese articulo.
 * ============================================================
 */
final class TipoAlimentoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query('
            SELECT ta.id_alimento, ta.nombre, ta.tipo, ta.descripcion,
                   ta.id_articulo,
                   a.codigo AS articulo_codigo,
                   a.nombre AS articulo_nombre,
                   a.unidad_medida AS articulo_unidad,
                   COALESCE(i.existencia, 0) AS articulo_existencia
            FROM tipo_alimento ta
            LEFT JOIN articulo a ON a.id_articulo = ta.id_articulo
            LEFT JOIN inventario i ON i.id_articulo = a.id_articulo
            ORDER BY ta.nombre ASC
        ');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT ta.*, a.codigo AS articulo_codigo, a.nombre AS articulo_nombre,
                   a.unidad_medida AS articulo_unidad,
                   COALESCE(i.existencia, 0) AS articulo_existencia
            FROM tipo_alimento ta
            LEFT JOIN articulo a ON a.id_articulo = ta.id_articulo
            LEFT JOIN inventario i ON i.id_articulo = a.id_articulo
            WHERE ta.id_alimento = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByNombre(string $nombre): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tipo_alimento WHERE nombre = :n LIMIT 1');
        $stmt->execute([':n' => $nombre]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM tipo_alimento WHERE id_alimento = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO tipo_alimento (nombre, id_articulo, tipo, descripcion)
            VALUES (:nombre, :id_articulo, :tipo, :descripcion)
        ');
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':id_articulo' => $data['id_articulo'] ?? null,
            ':tipo'        => $data['tipo'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE tipo_alimento
            SET nombre = :nombre,
                id_articulo = :id_articulo,
                tipo = :tipo,
                descripcion = :descripcion
            WHERE id_alimento = :id
        ');
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':id_articulo' => $data['id_articulo'] ?? null,
            ':tipo'        => $data['tipo'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null,
            ':id'          => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM tipo_alimento WHERE id_alimento = :id');
        $stmt->execute([':id' => $id]);
    }

    public function tieneAlimentaciones(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM alimentacion WHERE id_alimento = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}