<?php
declare(strict_types=1);

namespace App\Modules\Tanques;

use App\Http\Request;
use App\Http\Response;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class TanqueController
{
    public function __construct(private TanqueRepository $repo = new TanqueRepository()) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page     = max(1, Request::queryInt('page', 1));
        $limit    = min(100, max(1, Request::queryInt('limit', 20)));
        $estado   = Request::query('estado');
        $tipoAgua = Request::query('tipo_agua');
        $q        = Request::query('q');

        $result = $this->repo->paginate($page, $limit, $estado, $tipoAgua, $q);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $tanque = $this->repo->find((int) $params['id']);
        if (!$tanque) {
            Response::error('NOT_FOUND', 'Tanque no encontrado', 404);
            return;
        }
        Response::json(['data' => $tanque]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 80, 'Nombre')
            ->required('ubicacion', 'Ubicación')
            ->string('ubicacion', 1, 100, 'Ubicación')
            ->required('tipo_agua', 'Tipo de agua')
            ->string('tipo_agua', 1, 30, 'Tipo de agua');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!is_numeric($body['capacidad_litros'] ?? null) || (float) $body['capacidad_litros'] <= 0) {
            Response::error('VALIDATION', 'La capacidad en litros debe ser mayor a 0', 422);
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
            Response::error('NOT_FOUND', 'Tanque no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 80, 'Nombre')
            ->required('ubicacion', 'Ubicación')
            ->string('ubicacion', 1, 100, 'Ubicación')
            ->required('tipo_agua', 'Tipo de agua')
            ->string('tipo_agua', 1, 30, 'Tipo de agua');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!is_numeric($body['capacidad_litros'] ?? null) || (float) $body['capacidad_litros'] <= 0) {
            Response::error('VALIDATION', 'La capacidad en litros debe ser mayor a 0', 422);
            return;
        }

        $this->repo->update($id, $body);
        Response::json(['data' => $this->repo->find($id)]);
    }

    public function setParametros(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Tanque no encontrado', 404);
            return;
        }

        $body = Request::body();
        $temp = $body['temperatura_actual'] ?? null;
        $ph   = $body['ph_actual'] ?? null;

        if ($temp !== null && !is_numeric($temp)) {
            Response::error('VALIDATION', 'La temperatura debe ser numérica', 422);
            return;
        }
        if ($ph !== null && !is_numeric($ph)) {
            Response::error('VALIDATION', 'El pH debe ser numérico', 422);
            return;
        }
        if ($temp === null && $ph === null) {
            Response::error('VALIDATION', 'Debes enviar temperatura_actual o ph_actual', 422);
            return;
        }

        $this->repo->setParametros(
            $id,
            $temp !== null ? (float) $temp : null,
            $ph !== null ? (float) $ph : null
        );
        Response::json(['data' => $this->repo->find($id)]);
    }
}