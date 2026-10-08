<?php
declare(strict_types=1);

namespace App\Modules\Articulos;

use App\Database\Database;
use PDO;

final class ArticuloRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?int $idTipo, ?bool $activo, ?string $q, bool $stockBajo = false): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($idTipo !== null) {
            $where[] = 'a.id_tipo_articulo = :id_tipo';
            $params[':id_tipo'] = $idTipo;
        }
        if ($activo !== null) {
            $where[] = 'a.activo = :activo';
            $params[':activo'] = $activo ? 1 : 0;
        }
        if ($q !== null && $q !== '') {
            $where[] = '(a.codigo LIKE :q OR a.nombre LIKE :q OR a.marca LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($stockBajo) {
            $where[] = 'i.existencia <= a.stock_minimo';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM articulo a
            LEFT JOIN inventario i ON i.id_articulo = a.id_articulo
            {$whereSql}
        ");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT a.id_articulo, a.codigo, a.nombre, a.descripcion, a.categoria,
                   a.marca, a.unidad_medida, a.id_tipo_articulo, a.stock_minimo, a.activo,
                   t.nombre AS tipo_articulo,
                   COALESCE(i.existencia, 0) AS existencia
            FROM articulo a
            JOIN tipo_articulo t ON t.id_tipo_articulo = a.id_tipo_articulo
            LEFT JOIN inventario i ON i.id_articulo = a.id_articulo
            {$whereSql}
            ORDER BY a.id_articulo DESC
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
            SELECT a.*, t.nombre AS tipo_articulo,
                   COALESCE(i.existencia, 0) AS existencia
            FROM articulo a
            JOIN tipo_articulo t ON t.id_tipo_articulo = a.id_tipo_articulo
            LEFT JOIN inventario i ON i.id_articulo = a.id_articulo
            WHERE a.id_articulo = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM articulo WHERE codigo = :c LIMIT 1');
        $stmt->execute([':c' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM articulo WHERE id_articulo = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO articulo
                (codigo, nombre, descripcion, categoria, marca, unidad_medida,
                 id_tipo_articulo, stock_minimo, activo)
            VALUES
                (:codigo, :nombre, :descripcion, :categoria, :marca, :unidad_medida,
                 :id_tipo_articulo, :stock_minimo, :activo)
        ');
        $stmt->execute([
            ':codigo'           => $data['codigo'],
            ':nombre'           => $data['nombre'],
            ':descripcion'      => $data['descripcion'] ?? null,
            ':categoria'        => $data['categoria'] ?? null,
            ':marca'            => $data['marca'] ?? null,
            ':unidad_medida'    => $data['unidad_medida'],
            ':id_tipo_articulo' => $data['id_tipo_articulo'],
            ':stock_minimo'     => $data['stock_minimo'] ?? 0,
            ':activo'           => $data['activo'] ?? true ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE articulo SET
                codigo           = :codigo,
                nombre           = :nombre,
                descripcion      = :descripcion,
                categoria        = :categoria,
                marca            = :marca,
                unidad_medida    = :unidad_medida,
                id_tipo_articulo = :id_tipo_articulo,
                stock_minimo     = :stock_minimo,
                activo           = :activo
            WHERE id_articulo = :id
        ');
        $stmt->execute([
            ':codigo'           => $data['codigo'],
            ':nombre'           => $data['nombre'],
            ':descripcion'      => $data['descripcion'] ?? null,
            ':categoria'        => $data['categoria'] ?? null,
            ':marca'            => $data['marca'] ?? null,
            ':unidad_medida'    => $data['unidad_medida'],
            ':id_tipo_articulo' => $data['id_tipo_articulo'],
            ':stock_minimo'     => $data['stock_minimo'] ?? 0,
            ':activo'           => $data['activo'] ?? true ? 1 : 0,
            ':id'               => $id,
        ]);
    }

    public function setActivo(int $id, bool $activo): void
    {
        $stmt = $this->db->prepare('UPDATE articulo SET activo = :a WHERE id_articulo = :id');
        $stmt->execute([':a' => $activo ? 1 : 0, ':id' => $id]);
    }
}