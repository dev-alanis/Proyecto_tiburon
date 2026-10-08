<?php
declare(strict_types=1);

namespace App\Modules\Alimentacion;

use App\Database\Database;
use PDO;

/**
 * ============================================================
 *  AlimentacionRepository — Registro de alimentaciones
 * ============================================================
 *  Vinculación con inventario:
 *    - Cada tipo_alimento puede tener id_articulo.
 *    - Al crear una alimentación:
 *        1. Se busca el articulo vinculado.
 *        2. Se valida que exista stock suficiente.
 *        3. Se descuenta existencia.
 *        4. Se inserta el registro.
 *      Todo en una transacción.
 *    - Al actualizar/eliminar, se ajusta el inventario por delta.
 * ============================================================
 */
final class AlimentacionRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // ---------------------------------------------------------
    //  Lectura
    // ---------------------------------------------------------

    public function paginate(int $page, int $limit, ?int $idTanque, ?int $idAlimento, ?int $idEmpleado, ?string $desde, ?string $hasta): array
    {
        $offset = ($page - 1) * $limit;
        $where  = [];
        $params = [];

        if ($idTanque !== null) {
            $where[] = 'a.id_tanque = :id_tanque';
            $params[':id_tanque'] = $idTanque;
        }
        if ($idAlimento !== null) {
            $where[] = 'a.id_alimento = :id_alimento';
            $params[':id_alimento'] = $idAlimento;
        }
        if ($idEmpleado !== null) {
            $where[] = 'a.id_empleado = :id_empleado';
            $params[':id_empleado'] = $idEmpleado;
        }
        if ($desde !== null) {
            $where[] = 'a.fecha_hora >= :desde';
            $params[':desde'] = $desde . ' 00:00:00';
        }
        if ($hasta !== null) {
            $where[] = 'a.fecha_hora <= :hasta';
            $params[':hasta'] = $hasta . ' 23:59:59';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM alimentacion a {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT a.id_alimentacion, a.id_tanque, a.id_alimento, a.id_empleado,
                   a.fecha_hora, a.cantidad, a.observaciones,
                   t.nombre AS tanque_nombre,
                   ta.nombre AS alimento_nombre,
                   ta.id_articulo,
                   ar.codigo AS articulo_codigo,
                   ar.nombre AS articulo_nombre,
                   ar.unidad_medida AS articulo_unidad,
                   CONCAT(e.nombre, ' ', e.apellido_paterno) AS empleado_nombre
            FROM alimentacion a
            JOIN tanque t ON t.id_tanque = a.id_tanque
            JOIN tipo_alimento ta ON ta.id_alimento = a.id_alimento
            LEFT JOIN articulo ar ON ar.id_articulo = ta.id_articulo
            JOIN empleado e ON e.id_empleado = a.id_empleado
            {$whereSql}
            ORDER BY a.fecha_hora DESC
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
            SELECT a.*, t.nombre AS tanque_nombre,
                   ta.nombre AS alimento_nombre,
                   ta.id_articulo,
                   ar.codigo AS articulo_codigo,
                   ar.nombre AS articulo_nombre,
                   ar.unidad_medida AS articulo_unidad,
                   CONCAT(e.nombre, " ", e.apellido_paterno) AS empleado_nombre
            FROM alimentacion a
            JOIN tanque t ON t.id_tanque = a.id_tanque
            JOIN tipo_alimento ta ON ta.id_alimento = a.id_alimento
            LEFT JOIN articulo ar ON ar.id_articulo = ta.id_articulo
            JOIN empleado e ON e.id_empleado = a.id_empleado
            WHERE a.id_alimentacion = :id
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM alimentacion WHERE id_alimentacion = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    // ---------------------------------------------------------
    //  Helpers de inventario
    // ---------------------------------------------------------

    /** Devuelve el id_articulo vinculado al tipo_alimento, o null si no tiene. */
    public function articuloDeTipoAlimento(int $idAlimento): ?int
    {
        $stmt = $this->db->prepare('SELECT id_articulo FROM tipo_alimento WHERE id_alimento = :id');
        $stmt->execute([':id' => $idAlimento]);
        $val = $stmt->fetchColumn();
        return $val === null || $val === false ? null : (int) $val;
    }

    /** Existencia actual del artículo. */
    public function existencia(int $idArticulo): float
    {
        $stmt = $this->db->prepare('SELECT existencia FROM inventario WHERE id_articulo = :id');
        $stmt->execute([':id' => $idArticulo]);
        $val = $stmt->fetchColumn();
        return $val === false ? 0.0 : (float) $val;
    }

    /** Ajusta existencia en delta (puede ser negativo). Bloquea negativos. */
    public function ajustarExistencia(int $idArticulo, float $delta): void
    {
        $stmt = $this->db->prepare('
            UPDATE inventario
            SET existencia = existencia + :delta
            WHERE id_articulo = :id
        ');
        $stmt->execute([':delta' => $delta, ':id' => $idArticulo]);
    }

    // ---------------------------------------------------------
    //  Escritura (transaccional)
    // ---------------------------------------------------------

    /**
     * Crea una alimentación y descuenta inventario si el tipo tiene artículo.
     * Lanza \RuntimeException si no hay stock suficiente.
     */
    public function create(array $data): int
    {
        $idArticulo = $this->articuloDeTipoAlimento((int) $data['id_alimento']);

        $this->db->beginTransaction();
        try {
            if ($idArticulo !== null) {
                $existencia = $this->existencia($idArticulo);
                if ($existencia < (float) $data['cantidad']) {
                    $this->db->rollBack();
                    throw new \RuntimeException('STOCK_INSUFICIENTE');
                }
                $this->ajustarExistencia($idArticulo, -1 * (float) $data['cantidad']);
            }

            $stmt = $this->db->prepare('
                INSERT INTO alimentacion
                    (id_tanque, id_alimento, id_empleado, fecha_hora, cantidad, observaciones)
                VALUES
                    (:id_tanque, :id_alimento, :id_empleado, :fecha_hora, :cantidad, :observaciones)
            ');
            $stmt->execute([
                ':id_tanque'     => $data['id_tanque'],
                ':id_alimento'   => $data['id_alimento'],
                ':id_empleado'   => $data['id_empleado'],
                ':fecha_hora'    => $data['fecha_hora'] ?? date('Y-m-d H:i:s'),
                ':cantidad'      => $data['cantidad'],
                ':observaciones' => $data['observaciones'] ?? null,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una alimentación y ajusta el inventario por delta.
     * Si cambia el tipo de alimento, devuelve el stock al tipo anterior
     * y descuenta del nuevo.
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('SELECT * FROM alimentacion WHERE id_alimentacion = :id');
        $stmt->execute([':id' => $id]);
        $actual = $stmt->fetch();
        if (!$actual) {
            throw new \RuntimeException('NOT_FOUND');
        }

        $idArticuloViejo = $this->articuloDeTipoAlimento((int) $actual['id_alimento']);
        $idArticuloNuevo = $this->articuloDeTipoAlimento((int) $data['id_alimento']);

        $this->db->beginTransaction();
        try {
            // 1) Devolver al stock anterior
            if ($idArticuloViejo !== null) {
                $this->ajustarExistencia($idArticuloViejo, (float) $actual['cantidad']);
            }

            // 2) Descontar del nuevo
            if ($idArticuloNuevo !== null) {
                $existencia = $this->existencia($idArticuloNuevo);
                if ($existencia < (float) $data['cantidad']) {
                    $this->db->rollBack();
                    throw new \RuntimeException('STOCK_INSUFICIENTE');
                }
                $this->ajustarExistencia($idArticuloNuevo, -1 * (float) $data['cantidad']);
            }

            // 3) Update
            $stmt = $this->db->prepare('
                UPDATE alimentacion SET
                    id_tanque     = :id_tanque,
                    id_alimento   = :id_alimento,
                    id_empleado   = :id_empleado,
                    fecha_hora    = :fecha_hora,
                    cantidad      = :cantidad,
                    observaciones = :observaciones
                WHERE id_alimentacion = :id
            ');
            $stmt->execute([
                ':id_tanque'     => $data['id_tanque'],
                ':id_alimento'   => $data['id_alimento'],
                ':id_empleado'   => $data['id_empleado'],
                ':fecha_hora'    => $data['fecha_hora'] ?? date('Y-m-d H:i:s'),
                ':cantidad'      => $data['cantidad'],
                ':observaciones' => $data['observaciones'] ?? null,
                ':id'            => $id,
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una alimentación y devuelve el stock al artículo vinculado.
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('SELECT * FROM alimentacion WHERE id_alimentacion = :id');
        $stmt->execute([':id' => $id]);
        $actual = $stmt->fetch();
        if (!$actual) return;

        $idArticulo = $this->articuloDeTipoAlimento((int) $actual['id_alimento']);

        $this->db->beginTransaction();
        try {
            if ($idArticulo !== null) {
                $this->ajustarExistencia($idArticulo, (float) $actual['cantidad']);
            }

            $stmt = $this->db->prepare('DELETE FROM alimentacion WHERE id_alimentacion = :id');
            $stmt->execute([':id' => $id]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}