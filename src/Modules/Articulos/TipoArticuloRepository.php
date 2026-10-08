<?php
declare(strict_types=1);

namespace App\Modules\Articulos;

use App\Database\Database;
use PDO;

final class TipoArticuloRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $stmt = $this->db->query('
            SELECT id_tipo_articulo, nombre, descripcion
            FROM tipo_articulo
            ORDER BY nombre ASC
        ');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id_tipo_articulo, nombre, descripcion
            FROM tipo_articulo WHERE id_tipo_articulo = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByNombre(string $nombre): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tipo_articulo WHERE nombre = :n LIMIT 1');
        $stmt->execute([':n' => $nombre]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO tipo_articulo (nombre, descripcion)
            VALUES (:nombre, :descripcion)
        ');
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE tipo_articulo SET nombre = :nombre, descripcion = :descripcion
            WHERE id_tipo_articulo = :id
        ');
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':id'          => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM tipo_articulo WHERE id_tipo_articulo = :id');
        $stmt->execute([':id' => $id]);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM tipo_articulo WHERE id_tipo_articulo = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function tieneArticulos(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM articulo WHERE id_tipo_articulo = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}