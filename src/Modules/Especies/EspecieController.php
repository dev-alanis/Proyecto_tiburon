<?php
declare(strict_types=1);

namespace App\Modules\Especies;

use App\Http\Request;
use App\Http\Response;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class EspecieController
{
    public function __construct(private EspecieRepository $repo = new EspecieRepository()) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page  = max(1, Request::queryInt('page', 1));
        $limit = min(100, max(1, Request::queryInt('limit', 20)));
        $q     = Request::query('q');

        $result = $this->repo->paginate($page, $limit, $q);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $especie = $this->repo->find((int) $params['id']);
        if (!$especie) {
            Response::error('NOT_FOUND', 'Especie no encontrada', 404);
            return;
        }
        Response::json(['data' => $especie]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre_comun', 'Nombre común')
            ->string('nombre_comun', 1, 100, 'Nombre común')
            ->string('nombre_cientifico', 0, 150, 'Nombre científico')
            ->string('tipo_agua', 0, 30, 'Tipo de agua');

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
            Response::error('NOT_FOUND', 'Especie no encontrada', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre_comun', 'Nombre común')
            ->string('nombre_comun', 1, 100, 'Nombre común')
            ->string('nombre_cientifico', 0, 150, 'Nombre científico')
            ->string('tipo_agua', 0, 30, 'Tipo de agua');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
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
            Response::error('NOT_FOUND', 'Especie no encontrada', 404);
            return;
        }
        if ($this->repo->tienePeces($id)) {
            Response::error('CONFLICT', 'No se puede eliminar: tiene peces asociados', 409);
            return;
        }

        $this->repo->delete($id);
        Response::noContent();
    }
}