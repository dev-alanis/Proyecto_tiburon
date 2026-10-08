<?php
declare(strict_types=1);

namespace App\Modules\Inventario;

use App\Database\Database;
use PDO;

final class InventarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?bool $stockBajo): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($stockBajo === true) {
            $where[] = 'i.existencia <= a.stock_minimo';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM inventario i
            JOIN articulo a ON a.id_articulo = i.id_articulo
            {$whereSql}
        ");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT i.id_inventario, i.id_articulo, i.existencia, i.fecha_actualizacion,
                   a.codigo, a.nombre, a.unidad_medida, a.stock_minimo, a.activo
            FROM inventario i
            JOIN articulo a ON a.id_articulo = i.id_articulo
            {$whereSql}
            ORDER BY a.nombre ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function findByArticulo(int $idArticulo): ?array
    {
        $stmt = $this->db->prepare('
            SELECT i.*, a.codigo, a.nombre, a.unidad_medida, a.stock_minimo, a.activo
            FROM inventario i
            JOIN articulo a ON a.id_articulo = i.id_articulo
            WHERE i.id_articulo = :id
        ');
        $stmt->execute([':id' => $idArticulo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function ensure(int $idArticulo): void
    {
        $stmt = $this->db->prepare('
            INSERT IGNORE INTO inventario (id_articulo, existencia)
            VALUES (:id, 0)
        ');
        $stmt->execute([':id' => $idArticulo]);
    }

    public function setExistencia(int $idArticulo, float $existencia): void
    {
        $this->ensure($idArticulo);
        $stmt = $this->db->prepare('
            UPDATE inventario SET existencia = :e
            WHERE id_articulo = :id
        ');
        $stmt->execute([':e' => $existencia, ':id' => $idArticulo]);
    }

    public function ajustar(int $idArticulo, float $delta): void
    {
        $this->ensure($idArticulo);
        $stmt = $this->db->prepare('
            UPDATE inventario
            SET existencia = GREATEST(existencia + :delta, 0)
            WHERE id_articulo = :id
        ');
        $stmt->execute([':delta' => $delta, ':id' => $idArticulo]);
    }

    public function stockBajo(): array
    {
        $stmt = $this->db->query('
            SELECT i.id_articulo, i.existencia, a.codigo, a.nombre,
                   a.stock_minimo, a.unidad_medida
            FROM inventario i
            JOIN articulo a ON a.id_articulo = i.id_articulo
            WHERE i.existencia <= a.stock_minimo AND a.activo = 1
            ORDER BY i.existencia ASC
        ');
        return $stmt->fetchAll();
    }
}