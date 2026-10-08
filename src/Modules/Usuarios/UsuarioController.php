<?php
declare(strict_types=1);

namespace App\Modules\Usuarios;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Empleados\EmpleadoRepository;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class UsuarioController
{
    public function __construct(
        private UsuarioRepository  $repo = new UsuarioRepository(),
        private EmpleadoRepository $empleadoRepo = new EmpleadoRepository(),
    ) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $page   = max(1, Request::queryInt('page', 1));
        $limit  = min(100, max(1, Request::queryInt('limit', 20)));
        $activo = Request::queryBool('activo');
        $rol    = Request::query('rol');

        $result = $this->repo->paginate($page, $limit, $activo, $rol);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

        public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $usuario = $this->repo->find((int) $params['id']);
        if (!$usuario) {
            Response::error('NOT_FOUND', 'Usuario no encontrado', 404);
            return;
        }

        Response::json(['data' => $usuario]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_empleado', 'Empleado')
            ->required('nombre_usuario', 'Nombre de usuario')
            ->string('nombre_usuario', 3, 50, 'Nombre de usuario')
            ->required('password', 'Contraseña')
            ->string('password', 6, 100, 'Contraseña')
            ->required('rol', 'Rol')
            ->in('rol', Roles::ALL, 'Rol')
            ->bool('activo');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->empleadoRepo->exists((int) $body['id_empleado'])) {
            Response::error('VALIDATION', 'El empleado indicado no existe', 422);
            return;
        }
        if ($this->repo->existsEmpleadoVinculado((int) $body['id_empleado'])) {
            Response::error('CONFLICT', 'El empleado ya tiene un usuario asignado', 409);
            return;
        }
        if ($this->repo->findByNombreUsuario($body['nombre_usuario'])) {
            Response::error('CONFLICT', 'El nombre de usuario ya está en uso', 409);
            return;
        }

        $data = [
            'id_empleado'    => (int) $body['id_empleado'],
            'nombre_usuario' => $body['nombre_usuario'],
            'password_hash'  => Security::hashPassword($body['password']),
            'rol'            => $body['rol'],
            'activo'         => $body['activo'] ?? true,
        ];

        $id = $this->repo->create($data);
        Response::json(['data' => $this->repo->find($id)], 201);
    }

    public function update(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Usuario no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_empleado', 'Empleado')
            ->required('nombre_usuario', 'Nombre de usuario')
            ->string('nombre_usuario', 3, 50, 'Nombre de usuario')
            ->required('rol', 'Rol')
            ->in('rol', Roles::ALL, 'Rol')
            ->bool('activo');

        if (!empty($body['password'])) {
            $v->string('password', 6, 100, 'Contraseña');
        }

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->empleadoRepo->exists((int) $body['id_empleado'])) {
            Response::error('VALIDATION', 'El empleado indicado no existe', 422);
            return;
        }

        $existente = $this->repo->findByNombreUsuario($body['nombre_usuario']);
        if ($existente && (int) $existente['id_usuario'] !== $id) {
            Response::error('CONFLICT', 'El nombre de usuario ya está en uso', 409);
            return;
        }

        $data = [
            'id_empleado'    => (int) $body['id_empleado'],
            'nombre_usuario' => $body['nombre_usuario'],
            'rol'            => $body['rol'],
            'activo'         => $body['activo'] ?? true,
        ];

        if (!empty($body['password'])) {
            $data['password_hash'] = Security::hashPassword($body['password']);
        }

        $this->repo->update($id, $data);
        Response::json(['data' => $this->repo->find($id)]);
    }

    public function setActivo(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Usuario no encontrado', 404);
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