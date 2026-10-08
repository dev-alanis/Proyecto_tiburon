<?php
declare(strict_types=1);

namespace App\Modules\Inventario;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Articulos\ArticuloRepository;
use App\Security\Roles;
use App\Security\Security;

final class InventarioController
{
    public function __construct(
        private InventarioRepository $repo = new InventarioRepository(),
        private ArticuloRepository   $articuloRepo = new ArticuloRepository(),
    ) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page      = max(1, Request::queryInt('page', 1));
        $limit     = min(100, max(1, Request::queryInt('limit', 20)));
        $stockBajo = Request::queryBool('stock_bajo');

        $result = $this->repo->paginate($page, $limit, $stockBajo);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function show(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $idArticulo = (int) $params['idArticulo'];
        if (!$this->articuloRepo->exists($idArticulo)) {
            Response::error('NOT_FOUND', 'Artículo no encontrado', 404);
            return;
        }

        $this->repo->ensure($idArticulo);
        Response::json(['data' => $this->repo->findByArticulo($idArticulo)]);
    }

    public function ajustar(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN);

        $idArticulo = (int) $params['idArticulo'];
        if (!$this->articuloRepo->exists($idArticulo)) {
            Response::error('NOT_FOUND', 'Artículo no encontrado', 404);
            return;
        }

        $body = Request::body();
        $existencia = $body['existencia'] ?? null;

        if (!is_numeric($existencia) || (float) $existencia < 0) {
            Response::error('VALIDATION', 'La existencia debe ser un número mayor o igual a 0', 422);
            return;
        }

        $this->repo->setExistencia($idArticulo, (float) $existencia);
        Response::json(['data' => $this->repo->findByArticulo($idArticulo)]);
    }
}