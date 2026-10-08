<?php
declare(strict_types=1);

namespace App\Modules\Tanques;

use App\Database\Database;
use PDO;

final class TanqueRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?string $estado, ?string $tipoAgua, ?string $q): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($estado !== null && $estado !== '') {
            $where[] = 'estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($tipoAgua !== null && $tipoAgua !== '') {
            $where[] = 'tipo_agua = :ta';
            $params[':ta'] = $tipoAgua;
        }
        if ($q !== null && $q !== '') {
            $where[] = '(nombre LIKE :q OR ubicacion LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM tanque {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT id_tanque, nombre, capacidad_litros, ubicacion, tipo_agua,
                   temperatura_actual, ph_actual, estado
            FROM tanque
            {$whereSql}
            ORDER BY nombre ASC
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
        $stmt = $this->db->prepare('SELECT * FROM tanque WHERE id_tanque = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM tanque WHERE id_tanque = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO tanque
                (nombre, capacidad_litros, ubicacion, tipo_agua,
                 temperatura_actual, ph_actual, estado)
            VALUES
                (:nombre, :cap, :ubic, :ta, :temp, :ph, :estado)
        ');
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':cap'    => $data['capacidad_litros'],
            ':ubic'   => $data['ubicacion'],
            ':ta'     => $data['tipo_agua'],
            ':temp'   => $data['temperatura_actual'] ?? null,
            ':ph'     => $data['ph_actual'] ?? null,
            ':estado' => $data['estado'] ?? 'Activo',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE tanque SET
                nombre             = :nombre,
                capacidad_litros   = :cap,
                ubicacion          = :ubic,
                tipo_agua          = :ta,
                temperatura_actual = :temp,
                ph_actual          = :ph,
                estado             = :estado
            WHERE id_tanque = :id
        ');
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':cap'    => $data['capacidad_litros'],
            ':ubic'   => $data['ubicacion'],
            ':ta'     => $data['tipo_agua'],
            ':temp'   => $data['temperatura_actual'] ?? null,
            ':ph'     => $data['ph_actual'] ?? null,
            ':estado' => $data['estado'] ?? 'Activo',
            ':id'     => $id,
        ]);
    }

    public function setParametros(int $id, ?float $temperatura, ?float $ph): void
    {
        $stmt = $this->db->prepare('
            UPDATE tanque SET temperatura_actual = :t, ph_actual = :p
            WHERE id_tanque = :id
        ');
        $stmt->execute([':t' => $temperatura, ':p' => $ph, ':id' => $id]);
    }
}