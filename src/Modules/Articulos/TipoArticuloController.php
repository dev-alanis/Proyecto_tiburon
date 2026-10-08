<?php
declare(strict_types=1);

namespace App\Modules\Articulos;

use App\Http\Request;
use App\Http\Response;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class TipoArticuloController
{
    public function __construct(private TipoArticuloRepository $repo = new TipoArticuloRepository()) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);
        Response::json(['data' => $this->repo->all()]);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $item = $this->repo->find((int) $params['id']);
        if (!$item) {
            Response::error('NOT_FOUND', 'Tipo de artículo no encontrado', 404);
            return;
        }
        Response::json(['data' => $item]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 50, 'Nombre')
            ->string('descripcion', 0, 255, 'Descripción');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if ($this->repo->findByNombre($body['nombre'])) {
            Response::error('CONFLICT', 'Ya existe un tipo de artículo con ese nombre', 409);
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
            Response::error('NOT_FOUND', 'Tipo de artículo no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 50, 'Nombre')
            ->string('descripcion', 0, 255, 'Descripción');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        $existente = $this->repo->findByNombre($body['nombre']);
        if ($existente && (int) $existente['id_tipo_articulo'] !== $id) {
            Response::error('CONFLICT', 'Ya existe un tipo de artículo con ese nombre', 409);
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
            Response::error('NOT_FOUND', 'Tipo de artículo no encontrado', 404);
            return;
        }
        if ($this->repo->tieneArticulos($id)) {
            Response::error('CONFLICT', 'No se puede eliminar: tiene artículos asociados', 409);
            return;
        }

        $this->repo->delete($id);
        Response::noContent();
    }
}