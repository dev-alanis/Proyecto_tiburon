<?php
declare(strict_types=1);

namespace App\Modules\PezTanque;

use App\Http\Request;
use App\Http\Response;
use App\Modules\Peces\PezRepository;
use App\Modules\Tanques\TanqueRepository;
use App\Security\Roles;
use App\Security\Security;
use App\Support\Validator;

final class PezTanqueController
{
    public function __construct(
        private PezTanqueRepository $repo = new PezTanqueRepository(),
        private PezRepository       $pezRepo = new PezRepository(),
        private TanqueRepository    $tanqueRepo = new TanqueRepository(),
    ) {}

    public function index(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $page      = max(1, Request::queryInt('page', 1));
        $limit     = min(100, max(1, Request::queryInt('limit', 20)));
        $idPez     = Request::query('id_pez') !== null ? (int) Request::query('id_pez') : null;
        $idTanque  = Request::query('id_tanque') !== null ? (int) Request::query('id_tanque') : null;
        $activo    = Request::queryBool('activo');

        $result = $this->repo->paginate($page, $limit, $idPez, $idTanque, $activo);
        Response::paginated($result['data'], $page, $limit, $result['total']);
    }

    public function historialPez(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $idPez = (int) $params['idPez'];
        if (!$this->pezRepo->exists($idPez)) {
            Response::error('NOT_FOUND', 'Pez no encontrado', 404);
            return;
        }

        Response::json(['data' => $this->repo->historialDePez($idPez)]);
    }

    public function store(): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        $body = Request::body();

        $v = (new Validator($body))
            ->required('id_pez', 'Pez')
            ->required('id_tanque', 'Tanque')
            ->required('fecha_ingreso', 'Fecha de ingreso')
            ->string('observaciones', 0, 255, 'Observaciones');

        if ($v->fails()) {
            Response::error('VALIDATION', 'Datos inválidos', 422, $v->errors());
            return;
        }

        $idPez    = (int) $body['id_pez'];
        $idTanque = (int) $body['id_tanque'];

        if (!$this->pezRepo->exists($idPez)) {
            Response::error('VALIDATION', 'El pez indicado no existe', 422);
            return;
        }
        if (!$this->tanqueRepo->exists($idTanque)) {
            Response::error('VALIDATION', 'El tanque indicado no existe', 422);
            return;
        }

        // Regla: un pez solo puede tener una asignación activa
        $activa = $this->repo->findAsignacionActiva($idPez);
        if ($activa) {
            Response::error(
                'CONFLICT',
                'El pez ya tiene una asignación activa. Ciérrala antes de crear otra.',
                409,
                ['asignacion_activa' => $activa]
            );
            return;
        }

        $this->repo->asignar(
            $idPez,
            $idTanque,
            $body['fecha_ingreso'],
            $body['observaciones'] ?? null
        );

        Response::json([
            'data' => [
                'id_pez'        => $idPez,
                'id_tanque'     => $idTanque,
                'fecha_ingreso' => $body['fecha_ingreso'],
            ],
        ], 201);
    }

    public function cerrar(array $params): void
    {
        $user = Security::authenticate();
        Security::authorize($user, Roles::ADMIN, Roles::CUIDADOR);

        // Los parámetros de la clave primaria vienen por la URL
        $idPez        = (int) $params['idPez'];
        $idTanque     = (int) $params['idTanque'];
        $fechaIngreso = $params['fechaIngreso'];

        if (!$this->repo->existeAsignacion($idPez, $idTanque, $fechaIngreso)) {
            Response::error('NOT_FOUND', 'Asignación no encontrada', 404);
            return;
        }

        $body = Request::body();
        $fechaSalida = $body['fecha_salida'] ?? null;

        if (!is_string($fechaSalida) || $fechaSalida === '') {
            Response::error('VALIDATION', 'fecha_salida es obligatoria', 422);
            return;
        }
        if ($fechaSalida < $fechaIngreso) {
            Response::error('VALIDATION', 'fecha_salida debe ser posterior a fecha_ingreso', 422);
            return;
        }

        $afectadas = $this->repo->cerrar(
            $idPez,
            $idTanque,
            $fechaIngreso,
            $fechaSalida,
            $body['observaciones'] ?? null
        );

        if ($afectadas === 0) {
            Response::error('CONFLICT', 'La asignación ya estaba cerrada', 409);
            return;
        }

        Response::json([
            'data' => [
                'id_pez'        => $idPez,
                'id_tanque'     => $idTanque,
                'fecha_ingreso' => $fechaIngreso,
                'fecha_salida'  => $fechaSalida,
            ],
        ]);
    }
}