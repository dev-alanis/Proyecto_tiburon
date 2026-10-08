<?php
declare(strict_types=1);

namespace App\Modules\Empleados;

use App\Http\Request;
use App\Http\Response;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class EmpleadoController
{
    public function __construct(private EmpleadoRepository $repo = new EmpleadoRepository()) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $page   = max(1, Request::queryInt('page', 1));
        $limit  = min(100, max(1, Request::queryInt('limit', 20)));
        $activo = Request::queryBool('activo');
        $q      = Request::query('q');

        $result = $this->repo->paginate($page, $limit, $activo, $q);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $empleado = $this->repo->find((int) $params['id']);
        if (!$empleado) {
            Response::error('NOT_FOUND', 'Empleado no encontrado', 404);
            return;
        }

        Response::json(['data' => $empleado]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 50, 'Nombre')
            ->required('apellido_paterno', 'Apellido paterno')
            ->string('apellido_paterno', 1, 50, 'Apellido paterno')
            ->string('apellido_materno', 0, 50, 'Apellido materno')
            ->string('telefono', 0, 15, 'Teléfono')
            ->email('correo')
            ->required('puesto', 'Puesto')
            ->string('puesto', 1, 80, 'Puesto')
            ->date('fecha_ingreso', 'Fecha de ingreso')
            ->bool('activo');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
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
            Response::error('NOT_FOUND', 'Empleado no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 50, 'Nombre')
            ->required('apellido_paterno', 'Apellido paterno')
            ->string('apellido_paterno', 1, 50, 'Apellido paterno')
            ->string('apellido_materno', 0, 50, 'Apellido materno')
            ->string('telefono', 0, 15, 'Teléfono')
            ->email('correo')
            ->required('puesto', 'Puesto')
            ->string('puesto', 1, 80, 'Puesto')
            ->date('fecha_ingreso', 'Fecha de ingreso')
            ->bool('activo');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        $this->repo->update($id, $body);
        Response::json(['data' => $this->repo->find($id)]);
    }

   public function setActivo(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Empleado no encontrado', 404);
            return;
        }

        $body   = Request::body();
        $activo = $body['activo'] ?? null;

        if (!is_bool($activo)) {
            Response::error('VALIDATION', 'El campo activo es obligatorio y booleano', 422);
            return;
        }

        $this->repo->setActivo($id, $activo);
        Response::json(['data' => $this->repo->find($id)]);
    }
}