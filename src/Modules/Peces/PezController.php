<?php
declare(strict_types=1);

namespace App\Modules\Peces;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Especies\EspecieRepository;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class PezController
{
    private const ESTADOS = ['Activo', 'Enfermo', 'Cuarentena', 'Fallecido', 'Transferido'];

    public function __construct(
        private PezRepository     $repo = new PezRepository(),
        private EspecieRepository $especieRepo = new EspecieRepository(),
    ) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page      = max(1, Request::queryInt('page', 1));
        $limit     = min(100, max(1, Request::queryInt('limit', 20)));
        $idEspecie = Request::query('id_especie') !== null ? (int) Request::query('id_especie') : null;
        $estado    = Request::query('estado');
        $q         = Request::query('q');

        $result = $this->repo->paginate($page, $limit, $idEspecie, $estado, $q);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $pez = $this->repo->find((int) $params['id']);
        if (!$pez) {
            Response::error('NOT_FOUND', 'Pez no encontrado', 404);
            return;
        }
        Response::json(['data' => $pez]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_especie', 'Especie')
            ->string('nombre', 0, 50, 'Nombre')
            ->string('sexo', 0, 20, 'Sexo')
            ->required('fecha_ingreso', 'Fecha de ingreso')
            ->date('fecha_ingreso', 'Fecha de ingreso')
            ->date('fecha_nacimiento', 'Fecha de nacimiento');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->especieRepo->exists((int) $body['id_especie'])) {
            Response::error('VALIDATION', 'La especie indicada no existe', 422);
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
            Response::error('NOT_FOUND', 'Pez no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_especie', 'Especie')
            ->string('nombre', 0, 50, 'Nombre')
            ->string('sexo', 0, 20, 'Sexo')
            ->required('fecha_ingreso', 'Fecha de ingreso')
            ->date('fecha_ingreso', 'Fecha de ingreso')
            ->date('fecha_nacimiento', 'Fecha de nacimiento');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->especieRepo->exists((int) $body['id_especie'])) {
            Response::error('VALIDATION', 'La especie indicada no existe', 422);
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
            Response::error('NOT_FOUND', 'Pez no encontrado', 404);
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