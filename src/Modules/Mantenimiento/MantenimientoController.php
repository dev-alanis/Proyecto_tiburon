<?php
declare(strict_types=1);

namespace App\Modules\Mantenimiento;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Empleados\EmpleadoRepository;
use App\Modules\Tanques\TanqueRepository;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class MantenimientoController
{
    public function __construct(
        private MantenimientoRepository        $repo = new MantenimientoRepository(),
        private TipoMantenimientoRepository    $tipoRepo = new TipoMantenimientoRepository(),
        private TanqueRepository               $tanqueRepo = new TanqueRepository(),
        private EmpleadoRepository             $empleadoRepo = new EmpleadoRepository(),
    ) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page       = max(1, Request::queryInt('page', 1));
        $limit      = min(100, max(1, Request::queryInt('limit', 20)));
        $idTanque   = Request::query('id_tanque') !== null ? (int) Request::query('id_tanque') : null;
        $idTipo     = Request::query('id_tipo_mantenimiento') !== null ? (int) Request::query('id_tipo_mantenimiento') : null;
        $idEmpleado = Request::query('id_empleado') !== null ? (int) Request::query('id_empleado') : null;
        $desde      = Request::query('fecha_desde');
        $hasta      = Request::query('fecha_hasta');

        $result = $this->repo->paginate($page, $limit, $idTanque, $idTipo, $idEmpleado, $desde, $hasta);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $m = $this->repo->find((int) $params['id']);
        if (!$m) {
            Response::error('NOT_FOUND', 'Mantenimiento no encontrado', 404);
            return;
        }
        Response::json(['data' => $m]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_tanque', 'Tanque')
            ->required('id_tipo_mantenimiento', 'Tipo de mantenimiento')
            ->required('id_empleado', 'Empleado')
            ->string('observaciones', 0, 5000, 'Observaciones');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->tanqueRepo->exists((int) $body['id_tanque'])) {
            Response::error('VALIDATION', 'El tanque indicado no existe', 422);
            return;
        }
        if (!$this->tipoRepo->exists((int) $body['id_tipo_mantenimiento'])) {
            Response::error('VALIDATION', 'El tipo de mantenimiento no existe', 422);
            return;
        }
        if (!$this->empleadoRepo->exists((int) $body['id_empleado'])) {
            Response::error('VALIDATION', 'El empleado indicado no existe', 422);
            return;
        }

        $id = $this->repo->create($body);
        Response::json(['data' => $this->repo->find($id)], 201);
    }

    public function update(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Mantenimiento no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_tanque', 'Tanque')
            ->required('id_tipo_mantenimiento', 'Tipo de mantenimiento')
            ->required('id_empleado', 'Empleado')
            ->string('observaciones', 0, 5000, 'Observaciones');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->tanqueRepo->exists((int) $body['id_tanque'])) {
            Response::error('VALIDATION', 'El tanque indicado no existe', 422);
            return;
        }
        if (!$this->tipoRepo->exists((int) $body['id_tipo_mantenimiento'])) {
            Response::error('VALIDATION', 'El tipo de mantenimiento no existe', 422);
            return;
        }
        if (!$this->empleadoRepo->exists((int) $body['id_empleado'])) {
            Response::error('VALIDATION', 'El empleado indicado no existe', 422);
            return;
        }

        $this->repo->update($id, $body);
        Response::json(['data' => $this->repo->find($id)]);
    }

    public function destroy(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Mantenimiento no encontrado', 404);
            return;
        }

        $this->repo->delete($id);
        Response::noContent();
    }
}