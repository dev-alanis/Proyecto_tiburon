<?php
declare(strict_types=1);

namespace App\Modules\Articulos;

use App\Http\Request;
use App\Http\Response;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class ArticuloController
{
    public function __construct(
        private ArticuloRepository     $repo = new ArticuloRepository(),
        private TipoArticuloRepository $tipoRepo = new TipoArticuloRepository(),
    ) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page      = max(1, Request::queryInt('page', 1));
        $limit     = min(100, max(1, Request::queryInt('limit', 20)));
        $idTipo    = Request::query('id_tipo_articulo') !== null
                        ? (int) Request::query('id_tipo_articulo') : null;
        $activo    = Request::queryBool('activo');
        $q         = Request::query('q');
        $stockBajo = Request::queryBool('stock_bajo') === true;

        $result = $this->repo->paginate($page, $limit, $idTipo, $activo, $q, $stockBajo);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $articulo = $this->repo->find((int) $params['id']);
        if (!$articulo) {
            Response::error('NOT_FOUND', 'Artículo no encontrado', 404);
            return;
        }
        Response::json(['data' => $articulo]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('codigo', 'Código')
            ->string('codigo', 1, 30, 'Código')
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 100, 'Nombre')
            ->string('descripcion', 0, 255, 'Descripción')
            ->string('categoria', 0, 80, 'Categoría')
            ->string('marca', 0, 100, 'Marca')
            ->required('unidad_medida', 'Unidad de medida')
            ->string('unidad_medida', 1, 30, 'Unidad de medida')
            ->required('id_tipo_articulo', 'Tipo de artículo')
            ->bool('activo');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->tipoRepo->exists((int) $body['id_tipo_articulo'])) {
            Response::error('VALIDATION', 'El tipo de artículo indicado no existe', 422);
            return;
        }
        if ($this->repo->findByCodigo($body['codigo'])) {
            Response::error('CONFLICT', 'Ya existe un artículo con ese código', 409);
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
            Response::error('NOT_FOUND', 'Artículo no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('codigo', 'Código')
            ->string('codigo', 1, 30, 'Código')
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 100, 'Nombre')
            ->string('descripcion', 0, 255, 'Descripción')
            ->string('categoria', 0, 80, 'Categoría')
            ->string('marca', 0, 100, 'Marca')
            ->required('unidad_medida', 'Unidad de medida')
            ->string('unidad_medida', 1, 30, 'Unidad de medida')
            ->required('id_tipo_articulo', 'Tipo de artículo')
            ->bool('activo');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!$this->tipoRepo->exists((int) $body['id_tipo_articulo'])) {
            Response::error('VALIDATION', 'El tipo de artículo indicado no existe', 422);
            return;
        }

        $existente = $this->repo->findByCodigo($body['codigo']);
        if ($existente && (int) $existente['id_articulo'] !== $id) {
            Response::error('CONFLICT', 'Ya existe un artículo con ese código', 409);
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
            Response::error('NOT_FOUND', 'Artículo no encontrado', 404);
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