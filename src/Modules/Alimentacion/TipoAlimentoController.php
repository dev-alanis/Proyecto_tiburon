<?php
declare(strict_types=1);

namespace App\Modules\Alimentacion;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Articulos\ArticuloRepository;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

/**
 * ============================================================
 *  TipoAlimentoController — Tipos de alimento
 * ============================================================
 *  Base URL : /api/v1/tipos-alimento
 *  Roles    : ADMIN (CRUD) · CUIDADOR (lectura)
 *
 *  Endpoints:
 *    GET    /api/v1/tipos-alimento          Listado (incluye artículo vinculado y existencia)
 *    POST   /api/v1/tipos-alimento          Crear
 *    GET    /api/v1/tipos-alimento/{id}     Consultar
 *    PUT    /api/v1/tipos-alimento/{id}     Actualizar
 *    DELETE /api/v1/tipos-alimento/{id}     Eliminar (409 si tiene alimentaciones)
 *
 *  Reglas de negocio:
 *    - nombre único.
 *    - id_articulo (opcional) debe existir en articulo.
 *    - Si id_articulo viene, la alimentación consumirá inventario.
 *    - Si id_articulo es null, el tipo no descuenta stock.
 *    - No eliminar si tiene alimentaciones asociadas.
 * ============================================================
 */
final class TipoAlimentoController
{
    public function __construct(
        private TipoAlimentoRepository $repo = new TipoAlimentoRepository(),
        private ArticuloRepository     $articuloRepo = new ArticuloRepository(),
    ) {}

    /** GET /api/v1/tipos-alimento · @roles ADMIN, CUIDADOR · 200 { data: [] } */
    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);
        Response::json(['data' => $this->repo->all()]);
    }

    /** GET /api/v1/tipos-alimento/{id} · @roles ADMIN, CUIDADOR · 404 NOT_FOUND */
    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $item = $this->repo->find((int) $params['id']);
        if (!$item) {
            Response::error('NOT_FOUND', 'Tipo de alimento no encontrado', 404);
            return;
        }
        Response::json(['data' => $item]);
    }

    /**
     * POST /api/v1/tipos-alimento
     * Body: { nombre, tipo?, descripcion?, id_articulo? }
     * @roles ADMIN · 201 Created · 409 CONFLICT · 422 VALIDATION
     */
    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 100, 'Nombre')
            ->string('tipo', 0, 50, 'Tipo')
            ->string('descripcion', 0, 255, 'Descripción');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!empty($body['id_articulo'])) {
            if (!$this->articuloRepo->exists((int) $body['id_articulo'])) {
                Response::error('VALIDATION', 'El artículo indicado no existe', 422);
                return;
            }
        }

        if ($this->repo->findByNombre($body['nombre'])) {
            Response::error('CONFLICT', 'Ya existe un tipo de alimento con ese nombre', 409);
            return;
        }

        $id = $this->repo->create($body);
        Response::json(['data' => $this->repo->find($id)], 201);
    }

    /**
     * PUT /api/v1/tipos-alimento/{id}
     * Body: { nombre, tipo?, descripcion?, id_articulo? }
     * @roles ADMIN · 200 OK · 404 · 409 · 422
     */
    public function update(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Tipo de alimento no encontrado', 404);
            return;
        }

        $body = Request::body();

        $v = (new Validator($body))
            ->required('nombre', 'Nombre')
            ->string('nombre', 1, 100, 'Nombre')
            ->string('tipo', 0, 50, 'Tipo')
            ->string('descripcion', 0, 255, 'Descripción');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        if (!empty($body['id_articulo'])) {
            if (!$this->articuloRepo->exists((int) $body['id_articulo'])) {
                Response::error('VALIDATION', 'El artículo indicado no existe', 422);
                return;
            }
        }

        $existente = $this->repo->findByNombre($body['nombre']);
        if ($existente && (int) $existente['id_alimento'] !== $id) {
            Response::error('CONFLICT', 'Ya existe un tipo de alimento con ese nombre', 409);
            return;
        }

        $this->repo->update($id, $body);
        Response::json(['data' => $this->repo->find($id)]);
    }

    /** DELETE /api/v1/tipos-alimento/{id} · @roles ADMIN · 204 · 409 si tiene alimentaciones */
    public function destroy(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $id = (int) $params['id'];
        if (!$this->repo->exists($id)) {
            Response::error('NOT_FOUND', 'Tipo de alimento no encontrado', 404);
            return;
        }
        if ($this->repo->tieneAlimentaciones($id)) {
            Response::error('CONFLICT', 'No se puede eliminar: tiene alimentaciones asociadas', 409);
            return;
        }

        $this->repo->delete($id);
        Response::noContent();
    }
}