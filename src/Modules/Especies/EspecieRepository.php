<?php
declare(strict_types=1);

namespace App\Modules\Especies;

use App\Database\Database;
use PDO;

final class EspecieRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?string $q): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($q !== null && $q !== '') {
            $where[] = '(nombre_comun LIKE :q OR nombre_cientifico LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM especie {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT id_especie, nombre_comun, nombre_cientifico, descripcion,
                   tipo_agua, temperatura_min, temperatura_max, ph_min, ph_max
            FROM especie
            {$whereSql}
            ORDER BY nombre_comun ASC
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
        $stmt = $this->db->prepare('SELECT * FROM especie WHERE id_especie = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM especie WHERE id_especie = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO especie
                (nombre_comun, nombre_cientifico, descripcion, tipo_agua,
                 temperatura_min, temperatura_max, ph_min, ph_max)
            VALUES
                (:nc, :nci, :desc, :ta, :tmin, :tmax, :pmin, :pmax)
        ');
        $stmt->execute([
            ':nc'   => $data['nombre_comun'],
            ':nci'  => $data['nombre_cientifico'] ?? null,
            ':desc' => $data['descripcion'] ?? null,
            ':ta'   => $data['tipo_agua'] ?? null,
            ':tmin' => $data['temperatura_min'] ?? null,
            ':tmax' => $data['temperatura_max'] ?? null,
            ':pmin' => $data['ph_min'] ?? null,
            ':pmax' => $data['ph_max'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE especie SET
                nombre_comun      = :nc,
                nombre_cientifico = :nci,
                descripcion       = :desc,
                tipo_agua         = :ta,
                temperatura_min   = :tmin,
                temperatura_max   = :tmax,
                ph_min            = :pmin,
                ph_max            = :pmax
            WHERE id_especie = :id
        ');
        $stmt->execute([
            ':nc'   => $data['nombre_comun'],
            ':nci'  => $data['nombre_cientifico'] ?? null,
            ':desc' => $data['descripcion'] ?? null,
            ':ta'   => $data['tipo_agua'] ?? null,
            ':tmin' => $data['temperatura_min'] ?? null,
            ':tmax' => $data['temperatura_max'] ?? null,
            ':pmin' => $data['ph_min'] ?? null,
            ':pmax' => $data['ph_max'] ?? null,
            ':id'   => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM especie WHERE id_especie = :id');
        $stmt->execute([':id' => $id]);
    }

    public function tienePeces(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM pez WHERE id_especie = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}