<?php
declare(strict_types=1);

namespace App\Modules\PezTanque;

use App\Database\Database;
use PDO;

final class PezTanqueRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(int $page, int $limit, ?int $idPez, ?int $idTanque, ?bool $activo): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($idPez !== null) {
            $where[] = 'pt.id_pez = :id_pez';
            $params[':id_pez'] = $idPez;
        }
        if ($idTanque !== null) {
            $where[] = 'pt.id_tanque = :id_tanque';
            $params[':id_tanque'] = $idTanque;
        }
        if ($activo === true) {
            $where[] = 'pt.fecha_salida IS NULL';
        } elseif ($activo === false) {
            $where[] = 'pt.fecha_salida IS NOT NULL';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM pez_tanque pt {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT pt.id_pez, pt.id_tanque, pt.fecha_ingreso, pt.fecha_salida, pt.observaciones,
                   p.nombre AS pez_nombre,
                   e.nombre_comun AS especie,
                   t.nombre AS tanque_nombre
            FROM pez_tanque pt
            JOIN pez p ON p.id_pez = pt.id_pez
            JOIN especie e ON e.id_especie = p.id_especie
            JOIN tanque t ON t.id_tanque = pt.id_tanque
            {$whereSql}
            ORDER BY pt.fecha_ingreso DESC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function findAsignacionActiva(int $idPez): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM pez_tanque
            WHERE id_pez = :id AND fecha_salida IS NULL
            ORDER BY fecha_ingreso DESC LIMIT 1
        ');
        $stmt->execute([':id' => $idPez]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function asignar(int $idPez, int $idTanque, string $fechaIngreso, ?string $observaciones): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO pez_tanque (id_pez, id_tanque, fecha_ingreso, observaciones)
            VALUES (:id_pez, :id_tanque, :fecha_ingreso, :obs)
        ');
        $stmt->execute([
            ':id_pez'        => $idPez,
            ':id_tanque'     => $idTanque,
            ':fecha_ingreso' => $fechaIngreso,
            ':obs'           => $observaciones,
        ]);
    }

    public function cerrar(int $idPez, int $idTanque, string $fechaIngreso, string $fechaSalida, ?string $observaciones): int
    {
        $stmt = $this->db->prepare('
            UPDATE pez_tanque
            SET fecha_salida = :fecha_salida,
                observaciones = COALESCE(:obs, observaciones)
            WHERE id_pez = :id_pez
              AND id_tanque = :id_tanque
              AND fecha_ingreso = :fecha_ingreso
              AND fecha_salida IS NULL
        ');
        $stmt->execute([
            ':fecha_salida'  => $fechaSalida,
            ':obs'           => $observaciones,
            ':id_pez'        => $idPez,
            ':id_tanque'     => $idTanque,
            ':fecha_ingreso' => $fechaIngreso,
        ]);
        return $stmt->rowCount();
    }

    public function existeAsignacion(int $idPez, int $idTanque, string $fechaIngreso): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1 FROM pez_tanque
            WHERE id_pez = :id_pez AND id_tanque = :id_tanque AND fecha_ingreso = :fecha_ingreso
        ');
        $stmt->execute([
            ':id_pez'        => $idPez,
            ':id_tanque'     => $idTanque,
            ':fecha_ingreso' => $fechaIngreso,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function historialDePez(int $idPez): array
    {
        $stmt = $this->db->prepare('
            SELECT pt.*, t.nombre AS tanque_nombre
            FROM pez_tanque pt
            JOIN tanque t ON t.id_tanque = pt.id_tanque
            WHERE pt.id_pez = :id
            ORDER BY pt.fecha_ingreso DESC
        ');
        $stmt->execute([':id' => $idPez]);
        return $stmt->fetchAll();
    }
}