<?php
declare(strict_types=1);

namespace App\Modules\Alimentacion;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Empleados\EmpleadoRepository;
use App\Modules\Tanques\TanqueRepository;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

/**
 * ============================================================
 *  AlimentacionController — Registro de alimentaciones
 * ============================================================
 *  Base URL : /api/v1/alimentacion
 *  Roles    : ADMIN (CRUD) · CUIDADOR (crear, listar)
 *
 *  Endpoints:
 *    GET    /api/v1/alimentacion            Listado (id_tanque, id_alimento, id_empleado, fecha_desde, fecha_hasta)
 *    POST   /api/v1/alimentacion            Crear → descuenta inventario
 *    GET    /api/v1/alimentacion/{id}       Consultar
 *    PUT    /api/v1/alimentacion/{id}       Actualizar → ajusta inventario por delta
 *    DELETE /api/v1/alimentacion/{id}       Eliminar → devuelve stock
 *
 *  Reglas de negocio:
 *    - cantidad > 0.
 *    - id_tanque, id_alimento, id_empleado deben existir.
 *    - Si el tipo_alimento tiene id_articulo, se descuenta del inventario.
 *    - Si no hay stock suficiente, responde 409 STOCK_INSUFICIENTE.
 *    - Actualizar o eliminar ajusta el inventario para mantener consistencia.
 *    - CUIDADOR puede crear; solo ADMIN edita/elimina.
 * ============================================================
 */
final class AlimentacionController
{
    public function __construct(
        private AlimentacionRepository $repo = new AlimentacionRepository(),
        private TipoAlimentoRepository $tipoRepo = new TipoAlimentoRepository(),
        private TanqueRepository       $tanqueRepo = new TanqueRepository(),
        private EmpleadoRepository     $empleadoRepo = new EmpleadoRepository(),
    ) {}

    /** GET /api/v1/alimentacion · @roles ADMIN, CUIDADOR */
    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page       = max(1, Request::queryInt('page', 1));
        $limit      = min(100, max(1, Request::queryInt('limit', 20)));
        $idTanque   = Request::query('id_tanque') !== null ? (int) Request::query('id_tanque') : null;
        $idAlimento = Request::query('id_alimento') !== null ? (int) Request::query('id_alimento') : null;
        $idEmpleado = Request::query('id_empleado') !== null ? (int) Request::query('id_empleado') : null;
        $desde      = Request::query('fecha_desde');
        $hasta      = Request::query('fecha_hasta');

        $result = $this->repo->paginate($page, $limit, $idTanque, $idAlimento, $idEmpleado, $desde, $hasta);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    /** GET /api/v1/alimentacion/{id} · @roles ADMIN, CUIDADOR · 404 */
    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $a = $this->repo->find((int) $params['id']);
        if (!$a) {
            Response::error('NOT_FOUND', 'Registro de alimentación no encontrado', 404);
            return;
        }
        Response::json(['data' => $a]);
    }

    /**
     * POST /api/v1/alimentacion
     * Body: { id_tanque, id_alimento, id_empleado, cantidad, fecha_hora?, observaciones? }
     * @roles ADMIN, CUIDADOR · 201 · 409 STOCK_INSUFICIENTE · 422
     */
    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_tanque', 'Tanque')
            ->required('id_alimento', 'Alimento')
            ->required('id_empleado', 'Empleado')
            ->string('observaciones', 0, 255, 'Observaciones');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!is_numeric($body['cantidad'] ?? null) || (float) $body['cantidad'] <= 0) {
            Response::error('VALIDATION', 'La cantidad debe ser mayor a 0', 422);
            return;
        }

        if (!$this->tanqueRepo->exists((int) $body['id_tanque'])) {
            Response::error('VALIDATION', 'El tanque indicado no existe', 422);
            return;
        }
        if (!$this->tipoRepo->exists((int) $body['id_alimento'])) {
            Response::error('VALIDATION', 'El tipo de alimento no existe', 422);
            return;
        }
        if (!$this->empleadoRepo->exists((int) $body['id_empleado'])) {
            Response::error('VALIDATION', 'El empleado indicado no existe', 422);
            return;
        }

        try {
            $id = $this->repo->create($body);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'STOCK_INSUFICIENTE') {
                $idArticulo = $this->repo->articuloDeTipoAlimento((int) $body['id_alimento']);
                $existencia = $idArticulo !== null ? $this->repo->existencia($idArticulo) : 0;
                Response::error(
                    'STOCK_INSUFICIENTE',
                    'No hay stock suficiente para registrar la alimentación',
                    409,
                    [
                        'id_articulo'    => $idArticulo,
                        'existencia'     => $existencia,
                        'cantidad_solicitada' => (float) $body['cantidad'],
                    ]
                );
                return;
            }
            throw $e;
        }

        Response::json(['data' => $this->repo->find($id)], 201);
    }

    /**
     * PUT /api/v1/alimentacion/{id}
     * @roles ADMIN · 200 · 409 STOCK_INSUFICIENTE · 422
     */
    public function update(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Registro de alimentación no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_tanque', 'Tanque')
            ->required('id_alimento', 'Alimento')
            ->required('id_empleado', 'Empleado')
            ->string('observaciones', 0, 255, 'Observaciones');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!is_numeric($body['cantidad'] ?? null) || (float) $body['cantidad'] <= 0) {
            Response::error('VALIDATION', 'La cantidad debe ser mayor a 0', 422);
            return;
        }

        try {
            $this->repo->update($id, $body);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'STOCK_INSUFICIENTE') {
                Response::error('STOCK_INSUFICIENTE', 'No hay stock suficiente para la nueva cantidad', 409);
                return;
            }
            throw $e;
        }

        Response::json(['data' => $this->repo->find($id)]);
    }

    /** DELETE /api/v1/alimentacion/{id} · @roles ADMIN · 204 · devuelve stock */
    public function destroy(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Registro de alimentación no encontrado', 404);
            return;
        }

        $this->repo->delete($id);
        Response::noContent();
    }
}