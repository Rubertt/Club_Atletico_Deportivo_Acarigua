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
use App\Controllers\Web\PersonalController;
use App\Controllers\Web\AsistenciasController;
use App\Controllers\Web\MedidasAntropometricasController;
use App\Controllers\Web\ResultadosPruebasController;
use App\Controllers\Web\FichaMedicaController;
use App\Controllers\Web\ReportesController;
use App\Controllers\Web\ConfiguracionController;
use App\Controllers\Api\DireccionesApiController;
use App\Controllers\Api\AtletasApiController;
use App\Controllers\Api\MedidasAntropometricasApiController;
use App\Controllers\Api\ResultadosPruebasApiController;
use App\Controllers\Api\AsistenciasApiController;
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

    // Personal (sólo admin)
    $r->get('/personal',               [PersonalController::class, 'index'], [[RoleMiddleware::class, ['admin']]]);
    $r->get('/personal/crear',         [PersonalController::class, 'create'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/personal',              [PersonalController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->get('/personal/{id}/editar',   [PersonalController::class, 'edit'], [[RoleMiddleware::class, ['admin']]]);
    $r->post('/personal/{id}',         [PersonalController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);
    $r->post('/personal/{id}/eliminar', [PersonalController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin']]]);

    // Asistencias (admin + entrenador)
    $r->get('/asistencias',            [AsistenciasController::class, 'index']);
    $r->get('/asistencias/pase',       [AsistenciasController::class, 'pase']);
    $r->post('/asistencias/pase',      [AsistenciasController::class, 'guardarPase'], [CsrfMiddleware::class]);

    // Medidas Antropometricas
    $r->get('/medidas',              [MedidasAntropometricasController::class, 'index']);
    $r->get('/medidas/atleta/{id}',  [MedidasAntropometricasController::class, 'atleta']);
    $r->post('/medidas/atleta/{id}', [MedidasAntropometricasController::class, 'store'], [CsrfMiddleware::class]);

    // Pruebas físicas
    $r->get('/resultados-pruebas',                [ResultadosPruebasController::class, 'index']);
    $r->get('/resultados-pruebas/atleta/{id}',    [ResultadosPruebasController::class, 'atleta']);
    $r->post('/resultados-pruebas/atleta/{id}',   [ResultadosPruebasController::class, 'store'], [CsrfMiddleware::class]);

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
    $r->get('/direcciones/paises',                       [DireccionesApiController::class, 'paises']);
    $r->get('/direcciones/estados/{paisId}',             [DireccionesApiController::class, 'estados']);
    $r->get('/direcciones/municipios/{estadoId}',        [DireccionesApiController::class, 'municipios']);
    $r->get('/direcciones/parroquias/{municipioId}',     [DireccionesApiController::class, 'parroquias']);

    // Atletas (JSON para tablas y selects)
    $r->get('/atletas',            [AtletasApiController::class, 'index']);
    $r->get('/atletas/{id}',       [AtletasApiController::class, 'show']);

    // Antropometría (datos para gráficos)
    $r->get('/medidas/atleta/{id}', [MedidasAntropometricasApiController::class, 'historial']);

    // Pruebas físicas (datos para radar chart)
    $r->get('/resultados-pruebas/atleta/{id}',       [ResultadosPruebasApiController::class, 'historial']);

    // Asistencia (lista atletas por categoría para pase)
    $r->get('/asistencias/categoria/{id}', [AsistenciasApiController::class, 'atletasCategoria']);

    // Reportes (endpoints de datos agregados)
    $r->get('/reportes/resumen',          [ReportesApiController::class, 'resumen']);
});
