<?php
declare(strict_types=1);

use App\Config\Config;
use App\Http\Response;
use App\Http\Router;
use App\Modules\Empleados\EmpleadoController;
use App\Modules\Usuarios\UsuarioController;
use App\Security\Exceptions\AuthException;
use App\Modules\Articulos\ArticuloController;
use App\Modules\Articulos\TipoArticuloController;
use App\Modules\Especies\EspecieController;
use App\Modules\Inventario\InventarioController;
use App\Modules\Peces\PezController;
use App\Modules\Tanques\TanqueController;
use App\Modules\Auth\AuthController;
use App\Modules\Alimentacion\AlimentacionController;
use App\Modules\Alimentacion\TipoAlimentoController;
use App\Modules\Equipamiento\EquipamientoController;
use App\Modules\Mantenimiento\MantenimientoController;
use App\Modules\Mantenimiento\TipoMantenimientoController;
use App\Modules\PezTanque\PezTanqueController;

require __DIR__ . '/../vendor/autoload.php';

Config::load(__DIR__ . '/..');

$router = new Router();

// ---------- Empleados ----------
$router->get('/api/v1/empleados',               fn()  => (new EmpleadoController())->index());
$router->post('/api/v1/empleados',              fn()  => (new EmpleadoController())->store());
$router->get('/api/v1/empleados/{id}',          fn($p) => (new EmpleadoController())->show($p));
$router->put('/api/v1/empleados/{id}',          fn($p) => (new EmpleadoController())->update($p));
$router->patch('/api/v1/empleados/{id}/activo', fn($p) => (new EmpleadoController())->setActivo($p));

// ---------- Usuarios ----------
$router->get('/api/v1/usuarios',                fn()  => (new UsuarioController())->index());
$router->post('/api/v1/usuarios',               fn()  => (new UsuarioController())->store());
$router->get('/api/v1/usuarios/{id}',           fn($p) => (new UsuarioController())->show($p));
$router->put('/api/v1/usuarios/{id}',           fn($p) => (new UsuarioController())->update($p));
$router->patch('/api/v1/usuarios/{id}/activo',  fn($p) => (new UsuarioController())->setActivo($p));

// ---------- Tipos de artículo ----------
$router->get('/api/v1/tipos-articulo',          fn()  => (new TipoArticuloController())->index());
$router->post('/api/v1/tipos-articulo',         fn()  => (new TipoArticuloController())->store());
$router->get('/api/v1/tipos-articulo/{id}',     fn($p) => (new TipoArticuloController())->show($p));
$router->put('/api/v1/tipos-articulo/{id}',     fn($p) => (new TipoArticuloController())->update($p));
$router->delete('/api/v1/tipos-articulo/{id}',  fn($p) => (new TipoArticuloController())->destroy($p));

// ---------- Artículos ----------
$router->get('/api/v1/articulos',               fn()  => (new ArticuloController())->index());
$router->post('/api/v1/articulos',              fn()  => (new ArticuloController())->store());
$router->get('/api/v1/articulos/{id}',          fn($p) => (new ArticuloController())->show($p));
$router->put('/api/v1/articulos/{id}',          fn($p) => (new ArticuloController())->update($p));
$router->patch('/api/v1/articulos/{id}/activo', fn($p) => (new ArticuloController())->setActivo($p));

// ---------- Inventario ----------
$router->get('/api/v1/inventario',                     fn()  => (new InventarioController())->index());
$router->get('/api/v1/inventario/{idArticulo}',        fn($p) => (new InventarioController())->show($p));
$router->patch('/api/v1/inventario/{idArticulo}',      fn($p) => (new InventarioController())->ajustar($p));

// ---------- Especies ----------
$router->get('/api/v1/especies',                fn()  => (new EspecieController())->index());
$router->post('/api/v1/especies',               fn()  => (new EspecieController())->store());
$router->get('/api/v1/especies/{id}',           fn($p) => (new EspecieController())->show($p));
$router->put('/api/v1/especies/{id}',           fn($p) => (new EspecieController())->update($p));
$router->delete('/api/v1/especies/{id}',        fn($p) => (new EspecieController())->destroy($p));

// ---------- Peces ----------
$router->get('/api/v1/peces',                   fn()  => (new PezController())->index());
$router->post('/api/v1/peces',                  fn()  => (new PezController())->store());
$router->get('/api/v1/peces/{id}',              fn($p) => (new PezController())->show($p));
$router->put('/api/v1/peces/{id}',              fn($p) => (new PezController())->update($p));
$router->patch('/api/v1/peces/{id}/estado',     fn($p) => (new PezController())->setEstado($p));

// ---------- Tanques ----------
$router->get('/api/v1/tanques',                 fn()  => (new TanqueController())->index());
$router->post('/api/v1/tanques',                fn()  => (new TanqueController())->store());
$router->get('/api/v1/tanques/{id}',            fn($p) => (new TanqueController())->show($p));
$router->put('/api/v1/tanques/{id}',            fn($p) => (new TanqueController())->update($p));
$router->patch('/api/v1/tanques/{id}/parametros', fn($p) => (new TanqueController())->setParametros($p));

// ---------- Auth ----------
$router->post('/api/v1/auth/login',    fn()  => (new AuthController())->login());
$router->post('/api/v1/auth/refresh',  fn()  => (new AuthController())->refresh());
$router->post('/api/v1/auth/logout',   fn()  => (new AuthController())->logout());
$router->get('/api/v1/auth/me',        fn()  => (new AuthController())->me());

// ---------- Equipamiento ----------
$router->get('/api/v1/equipamiento',                fn()  => (new EquipamientoController())->index());
$router->post('/api/v1/equipamiento',               fn()  => (new EquipamientoController())->store());
$router->get('/api/v1/equipamiento/{id}',           fn($p) => (new EquipamientoController())->show($p));
$router->put('/api/v1/equipamiento/{id}',           fn($p) => (new EquipamientoController())->update($p));
$router->patch('/api/v1/equipamiento/{id}/estado',  fn($p) => (new EquipamientoController())->setEstado($p));

// ---------- Tipos de mantenimiento ----------
$router->get('/api/v1/tipos-mantenimiento',            fn()  => (new TipoMantenimientoController())->index());
$router->post('/api/v1/tipos-mantenimiento',           fn()  => (new TipoMantenimientoController())->store());
$router->get('/api/v1/tipos-mantenimiento/{id}',       fn($p) => (new TipoMantenimientoController())->show($p));
$router->put('/api/v1/tipos-mantenimiento/{id}',       fn($p) => (new TipoMantenimientoController())->update($p));
$router->delete('/api/v1/tipos-mantenimiento/{id}',    fn($p) => (new TipoMantenimientoController())->destroy($p));

// ---------- Mantenimiento ----------
$router->get('/api/v1/mantenimientos',           fn()  => (new MantenimientoController())->index());
$router->post('/api/v1/mantenimientos',          fn()  => (new MantenimientoController())->store());
$router->get('/api/v1/mantenimientos/{id}',      fn($p) => (new MantenimientoController())->show($p));
$router->put('/api/v1/mantenimientos/{id}',      fn($p) => (new MantenimientoController())->update($p));
$router->delete('/api/v1/mantenimientos/{id}',   fn($p) => (new MantenimientoController())->destroy($p));

// ---------- Tipos de alimento ----------
$router->get('/api/v1/tipos-alimento',           fn()  => (new TipoAlimentoController())->index());
$router->post('/api/v1/tipos-alimento',          fn()  => (new TipoAlimentoController())->store());
$router->get('/api/v1/tipos-alimento/{id}',      fn($p) => (new TipoAlimentoController())->show($p));
$router->put('/api/v1/tipos-alimento/{id}',      fn($p) => (new TipoAlimentoController())->update($p));
$router->delete('/api/v1/tipos-alimento/{id}',   fn($p) => (new TipoAlimentoController())->destroy($p));

// ---------- Alimentación ----------
$router->get('/api/v1/alimentacion',             fn()  => (new AlimentacionController())->index());
$router->post('/api/v1/alimentacion',            fn()  => (new AlimentacionController())->store());
$router->get('/api/v1/alimentacion/{id}',        fn($p) => (new AlimentacionController())->show($p));
$router->put('/api/v1/alimentacion/{id}',        fn($p) => (new AlimentacionController())->update($p));
$router->delete('/api/v1/alimentacion/{id}',     fn($p) => (new AlimentacionController())->destroy($p));

// ---------- Pez–Tanque ----------
$router->get('/api/v1/pez-tanque',                                     fn()  => (new PezTanqueController())->index());
$router->post('/api/v1/pez-tanque',                                    fn()  => (new PezTanqueController())->store());
$router->get('/api/v1/pez-tanque/historial/{idPez}',                   fn($p) => (new PezTanqueController())->historialPez($p));
$router->patch('/api/v1/pez-tanque/{idPez}/{idTanque}/{fechaIngreso}', fn($p) => (new PezTanqueController())->cerrar($p));

// ---------- Dispatch ----------
try {
    $router->dispatch();
} catch (AuthException $e) {
    Response::error($e->getErrorCode(), $e->getMessage(), $e->getStatusCode());
} catch (\Throwable $e) {
    $debug = Config::get('app_env') === 'development';
    Response::error(
        'INTERNAL_ERROR',
        $debug ? $e->getMessage() : 'Error interno',
        500,
        $debug ? ['trace' => $e->getTraceAsString()] : []
    );
}