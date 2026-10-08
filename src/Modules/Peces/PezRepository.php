<?php
declare(strict_types=1);

namespace App\Modules\Peces;

use App\Database\Database;
use PDO;

final class PezRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?int $idEspecie, ?string $estado, ?string $q): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($idEspecie !== null) {
            $where[] = 'p.id_especie = :id_especie';
            $params[':id_especie'] = $idEspecie;
        }
        if ($estado !== null && $estado !== '') {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($q !== null && $q !== '') {
            $where[] = 'p.nombre LIKE :q';
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM pez p {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT p.id_pez, p.id_especie, p.nombre, p.sexo, p.fecha_ingreso,
                   p.fecha_nacimiento, p.estado,
                   e.nombre_comun AS especie
            FROM pez p
            JOIN especie e ON e.id_especie = p.id_especie
            {$whereSql}
            ORDER BY p.id_pez DESC
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
            SELECT p.*, e.nombre_comun AS especie
            FROM pez p
            JOIN especie e ON e.id_especie = p.id_especie
            WHERE p.id_pez = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM pez WHERE id_pez = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO pez
                (id_especie, nombre, sexo, fecha_ingreso, fecha_nacimiento, estado)
            VALUES
                (:id_especie, :nombre, :sexo, :fecha_ingreso, :fecha_nacimiento, :estado)
        ');
        $stmt->execute([
            ':id_especie'      => $data['id_especie'],
            ':nombre'          => $data['nombre'] ?? null,
            ':sexo'            => $data['sexo'] ?? null,
            ':fecha_ingreso'   => $data['fecha_ingreso'],
            ':fecha_nacimiento'=> $data['fecha_nacimiento'] ?? null,
            ':estado'          => $data['estado'] ?? 'Activo',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE pez SET
                id_especie       = :id_especie,
                nombre           = :nombre,
                sexo             = :sexo,
                fecha_ingreso    = :fecha_ingreso,
                fecha_nacimiento = :fecha_nacimiento,
                estado           = :estado
            WHERE id_pez = :id
        ');
        $stmt->execute([
            ':id_especie'      => $data['id_especie'],
            ':nombre'          => $data['nombre'] ?? null,
            ':sexo'            => $data['sexo'] ?? null,
            ':fecha_ingreso'   => $data['fecha_ingreso'],
            ':fecha_nacimiento'=> $data['fecha_nacimiento'] ?? null,
            ':estado'          => $data['estado'] ?? 'Activo',
            ':id'              => $id,
        ]);
    }

    public function setEstado(int $id, string $estado): void
    {
        $stmt = $this->db->prepare('UPDATE pez SET estado = :e WHERE id_pez = :id');
        $stmt->execute([':e' => $estado, ':id' => $id]);
    }
}