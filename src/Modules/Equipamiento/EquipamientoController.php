<?php
declare(strict_types=1);

namespace App\Modules\Equipamiento;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Tanques\TanqueRepository;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class EquipamientoController
{
    private const ESTADOS = ['Activo', 'Mantenimiento', 'Fuera de servicio', 'Retirado'];

    public function __construct(
        private EquipamientoRepository $repo = new EquipamientoRepository(),
        private TanqueRepository       $tanqueRepo = new TanqueRepository(),
    ) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page     = max(1, Request::queryInt('page', 1));
        $limit    = min(100, max(1, Request::queryInt('limit', 20)));
        $idTanque = Request::query('id_tanque') !== null ? (int) Request::query('id_tanque') : null;
        $estado   = Request::query('estado');

        $result = $this->repo->paginate($page, $limit, $idTanque, $estado);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $eq = $this->repo->find((int) $params['id']);
        if (!$eq) {
            Response::error('NOT_FOUND', 'Equipamiento no encontrado', 404);
            return;
        }
        Response::json(['data' => $eq]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_tanque', 'Tanque')
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 100, 'Nombre')
            ->string('tipo', 0, 50, 'Tipo')
            ->string('marca', 0, 80, 'Marca')
            ->string('modelo', 0, 80, 'Modelo')
            ->date('fecha_instalacion', 'Fecha de instalación')
            ->in('estado', self::ESTADOS, 'Estado');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->tanqueRepo->exists((int) $body['id_tanque'])) {
            Response::error('VALIDATION', 'El tanque indicado no existe', 422);
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
            Response::error('NOT_FOUND', 'Equipamiento no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_tanque', 'Tanque')
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 100, 'Nombre')
            ->string('tipo', 0, 50, 'Tipo')
            ->string('marca', 0, 80, 'Marca')
            ->string('modelo', 0, 80, 'Modelo')
            ->date('fecha_instalacion', 'Fecha de instalación')
            ->in('estado', self::ESTADOS, 'Estado');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->tanqueRepo->exists((int) $body['id_tanque'])) {
            Response::error('VALIDATION', 'El tanque indicado no existe', 422);
            return;
        }

        $this->repo->update($id, $body);
        Response::json(['data' => $this->repo->find($id)]);
    }

    public function setEstado(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Equipamiento no encontrado', 404);
            return;
        }

        $body   = Request::body();
        $estado = $body['estado'] ?? null;

        if (!is_string($estado) || !in_array($estado, self::ESTADOS, true)) {
            Response::error('VALIDATION', 'Estado inválido', 422, ['estado' => self::ESTADOS]);
            return;
        }

        $this->repo->setEstado($id, $estado);
        Response::json(['data' => $this->repo->find($id)]);
    }
}