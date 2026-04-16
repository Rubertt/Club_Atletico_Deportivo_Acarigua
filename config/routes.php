<?php
/**
 * Definición de rutas.
 * @var \App\Core\Router $router
 */
declare(strict_types=1);

use App\Controllers\Web\HomeController;
use App\Controllers\Web\AuthController;
use App\Controllers\Web\DashboardController;
use App\Controllers\Web\AtletasController;
use App\Controllers\Web\CategoriasController;
use App\Controllers\Web\PlantelController;
use App\Controllers\Web\AsistenciaController;
use App\Controllers\Web\AntropometriaController;
use App\Controllers\Web\PruebasController;
use App\Controllers\Web\FichaMedicaController;
use App\Controllers\Web\ReportesController;
use App\Controllers\Web\ConfiguracionController;
use App\Controllers\Api\UbicacionesApiController;
use App\Controllers\Api\AtletasApiController;
use App\Controllers\Api\AntropometriaApiController;
use App\Controllers\Api\PruebasApiController;
use App\Controllers\Api\AsistenciaApiController;
use App\Controllers\Api\ReportesApiController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\CsrfMiddleware;

// ---------------------------------------------------------------------------
// Rutas públicas
// ---------------------------------------------------------------------------
$router->get('/',           [HomeController::class, 'index']);
$router->get('/nosotros',   [HomeController::class, 'nosotros']);
$router->get('/contacto',   [HomeController::class, 'contacto']);
$router->post('/contacto',  [HomeController::class, 'enviarContacto'], [CsrfMiddleware::class]);

$router->get('/login',      [AuthController::class, 'showLogin']);
$router->post('/login',     [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/logout',     [AuthController::class, 'logout']);
$router->post('/logout',    [AuthController::class, 'logout'], [CsrfMiddleware::class]);
$router->get('/recuperar',  [AuthController::class, 'showRecuperar']);
$router->post('/recuperar', [AuthController::class, 'recuperar'], [CsrfMiddleware::class]);

// ---------------------------------------------------------------------------
// Panel admin (requiere autenticación)
// ---------------------------------------------------------------------------
$router->group('/admin', [AuthMiddleware::class], function ($r) {
    $r->get('',                   [DashboardController::class, 'index']);
    $r->get('/',                  [DashboardController::class, 'index']);

    // Atletas (lectura: todos autenticados; escritura: admin)
    $r->get('/atletas',           [AtletasController::class, 'index']);
    $r->get('/atletas/crear',     [AtletasController::class, 'create'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/atletas',          [AtletasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->get('/atletas/{id}',      [AtletasController::class, 'show']);
    $r->get('/atletas/{id}/editar', [AtletasController::class, 'edit'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/atletas/{id}',     [AtletasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->post('/atletas/{id}/eliminar', [AtletasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);

    // Categorías
    $r->get('/categorias',              [CategoriasController::class, 'index']);
    $r->get('/categorias/crear',        [CategoriasController::class, 'create'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/categorias',             [CategoriasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->get('/categorias/{id}/editar',  [CategoriasController::class, 'edit'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/categorias/{id}',        [CategoriasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->post('/categorias/{id}/eliminar', [CategoriasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);

    // Plantel (sólo admin)
    $r->get('/plantel',               [PlantelController::class, 'index'], [[RoleMiddleware::class, ['admin']]]);
    $r->get('/plantel/crear',         [PlantelController::class, 'create'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/plantel',              [PlantelController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->get('/plantel/{id}/editar',   [PlantelController::class, 'edit'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/plantel/{id}',         [PlantelController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->post('/plantel/{id}/eliminar', [PlantelController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);

    // Asistencia (admin + entrenador)
    $r->get('/asistencia',            [AsistenciaController::class, 'index']);
    $r->get('/asistencia/pase',       [AsistenciaController::class, 'pase']);
    $r->post('/asistencia/pase',      [AsistenciaController::class, 'guardarPase'], [CsrfMiddleware::class]);

    // Antropometría
    $r->get('/antropometria',              [AntropometriaController::class, 'index']);
    $r->get('/antropometria/atleta/{id}',  [AntropometriaController::class, 'atleta']);
    $r->post('/antropometria/atleta/{id}', [AntropometriaController::class, 'store'], [CsrfMiddleware::class]);

    // Pruebas físicas
    $r->get('/pruebas',                [PruebasController::class, 'index']);
    $r->get('/pruebas/atleta/{id}',    [PruebasController::class, 'atleta']);
    $r->post('/pruebas/atleta/{id}',   [PruebasController::class, 'store'], [CsrfMiddleware::class]);

    // Ficha médica (lectura entrenador; escritura admin)
    $r->get('/ficha-medica/{id}',      [FichaMedicaController::class, 'show']);
    $r->post('/ficha-medica/{id}',     [FichaMedicaController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);

    // Reportes
    $r->get('/reportes',                  [ReportesController::class, 'index']);
    $r->get('/reportes/atleta/{id}',      [ReportesController::class, 'fichaAtleta']);
    $r->get('/reportes/asistencia',       [ReportesController::class, 'asistencia']);
    $r->get('/reportes/categoria/{id}',   [ReportesController::class, 'categoria']);

    // Configuración (sólo admin)
    $r->get('/configuracion',         [ConfiguracionController::class, 'index'], [[RoleMiddleware::class, ['admin']]]);
    $r->get('/configuracion/usuarios', [ConfiguracionController::class, 'usuarios'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/configuracion/usuarios', [ConfiguracionController::class, 'guardarUsuario'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
});

// ---------------------------------------------------------------------------
// API REST (JSON, requiere auth salvo excepciones)
// ---------------------------------------------------------------------------
$router->group('/api', [AuthMiddleware::class], function ($r) {
    // Ubicaciones cascada
    $r->get('/ubicaciones/paises',                       [UbicacionesApiController::class, 'paises']);
    $r->get('/ubicaciones/estados/{paisId}',             [UbicacionesApiController::class, 'estados']);
    $r->get('/ubicaciones/municipios/{estadoId}',        [UbicacionesApiController::class, 'municipios']);
    $r->get('/ubicaciones/parroquias/{municipioId}',     [UbicacionesApiController::class, 'parroquias']);

    // Atletas (JSON para tablas y selects)
    $r->get('/atletas',            [AtletasApiController::class, 'index']);
    $r->get('/atletas/{id}',       [AtletasApiController::class, 'show']);

    // Antropometría (datos para gráficos)
    $r->get('/antropometria/atleta/{id}', [AntropometriaApiController::class, 'historial']);

    // Pruebas físicas (datos para radar chart)
    $r->get('/pruebas/atleta/{id}',       [PruebasApiController::class, 'historial']);

    // Asistencia (lista atletas por categoría para pase)
    $r->get('/asistencia/categoria/{id}', [AsistenciaApiController::class, 'atletasCategoria']);

    // Reportes (endpoints de datos agregados)
    $r->get('/reportes/resumen',          [ReportesApiController::class, 'resumen']);
});
