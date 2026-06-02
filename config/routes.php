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
use App\Controllers\Web\UsuariosController;
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
$router->get('/', [HomeController::class, 'index']);
$router->get('/nosotros', [HomeController::class, 'nosotros']);
$router->get('/contacto', [HomeController::class, 'contacto']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);
$router->get('/recuperar', [AuthController::class, 'showRecuperar']);
$router->post('/recuperar', [AuthController::class, 'recuperar'], [CsrfMiddleware::class]);
$router->get('/recuperar/preguntas', [AuthController::class, 'showPreguntas']);
$router->post('/recuperar/preguntas', [AuthController::class, 'verificarPreguntas'], [CsrfMiddleware::class]);
$router->get('/recuperar/nueva-clave', [AuthController::class, 'showNuevaClave']);
$router->post('/recuperar/nueva-clave', [AuthController::class, 'cambiarClave'], [CsrfMiddleware::class]);

use App\Controllers\Web\PerfilController;

// ---------------------------------------------------------------------------
// Panel admin (requiere autenticación)
// ---------------------------------------------------------------------------
$router->group('/admin', [AuthMiddleware::class], function ($r) {
    // Configuración inicial de seguridad obligatoria
    $r->get('/setup', [PerfilController::class, 'setup']);
    $r->post('/setup/save', [PerfilController::class, 'saveSetup'], [CsrfMiddleware::class]);
    $r->get('', [DashboardController::class, 'index']);
    $r->get('/', [DashboardController::class, 'index']);

    // Atletas (lectura: todos autenticados; escritura: admin)
    $r->get('/atletas', [AtletasController::class, 'index']);
    $r->get('/atletas/crear', [AtletasController::class, 'create'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/atletas', [AtletasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/atletas/validar-paso', [AtletasController::class, 'validarPaso'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/atletas/{id}', [AtletasController::class, 'show']);
    $r->get('/atletas/{id}/editar', [AtletasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/atletas/{id}', [AtletasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/atletas/{id}/eliminar', [AtletasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Categorías
    $r->get('/categorias', [CategoriasController::class, 'index']);
    $r->get('/categorias/crear', [CategoriasController::class, 'create'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias', [CategoriasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/categorias/{id}/editar', [CategoriasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias/{id}', [CategoriasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias/{id}/eliminar', [CategoriasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Asignaciones de Categorías
    $r->get('/categorias/{id}/detalles', [\App\Controllers\Web\AsigCategoriasController::class, 'index']);
    $r->get('/categorias/{id}/asignar', [\App\Controllers\Web\AsigCategoriasController::class, 'create'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias/{id}/asignar', [\App\Controllers\Web\AsigCategoriasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/asig-categorias/{id}/editar', [\App\Controllers\Web\AsigCategoriasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asig-categorias/{id}/editar', [\App\Controllers\Web\AsigCategoriasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asig-categorias/{id}/eliminar', [\App\Controllers\Web\AsigCategoriasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Usuarios (sólo admin)
    $r->get('/usuarios', [UsuariosController::class, 'index'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/usuarios/crear', [UsuariosController::class, 'create'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios', [UsuariosController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/usuarios/{id}/perfil', [UsuariosController::class, 'show'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/usuarios/{id}/editar', [UsuariosController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}', [UsuariosController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/update-basico', [UsuariosController::class, 'updateBasico'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/foto', [UsuariosController::class, 'updateFoto'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/direccion', [UsuariosController::class, 'updateDireccion'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/eliminar', [UsuariosController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/restablecer', [UsuariosController::class, 'restablecer'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Asistencias (admin + entrenador)
    $r->get('/asistencias', [AsistenciasController::class, 'index']);
    $r->get('/asistencias/crear', [AsistenciasController::class, 'crear']);
    $r->post('/asistencias/crear', [AsistenciasController::class, 'guardar'], [CsrfMiddleware::class]);
    $r->get('/asistencias/{id}', [AsistenciasController::class, 'show']);
    $r->get('/asistencias/{id}/editar', [AsistenciasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asistencias/{id}/editar', [AsistenciasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asistencias/{id}/eliminar', [AsistenciasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Medidas Antropometricas
    $r->get('/medidas', [MedidasAntropometricasController::class, 'index']);
    $r->get('/medidas/atleta/{id}', [MedidasAntropometricasController::class, 'atleta']);
    $r->post('/medidas/atleta/{id}', [MedidasAntropometricasController::class, 'store'], [CsrfMiddleware::class]);
    $r->post('/medidas/{id}/eliminar', [MedidasAntropometricasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/medidas/{id}/editar', [MedidasAntropometricasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);

    // Pruebas físicas
    $r->get('/resultados-pruebas', [ResultadosPruebasController::class, 'index']);
    $r->get('/resultados-pruebas/atleta/{id}', [ResultadosPruebasController::class, 'atleta']);
    $r->post('/resultados-pruebas/atleta/{id}', [ResultadosPruebasController::class, 'store'], [CsrfMiddleware::class]);
    $r->post('/resultados-pruebas/{id}/eliminar', [ResultadosPruebasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/resultados-pruebas/{id}/editar', [ResultadosPruebasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);

    // Ficha médica (lectura entrenador; escritura admin)
    $r->get('/ficha-medica/{id}', [FichaMedicaController::class, 'show']);
    $r->post('/ficha-medica/{id}', [FichaMedicaController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/ficha-medica/{id}/discapacidad', [FichaMedicaController::class, 'storeDiscapacidad'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/ficha-medica/{id}/discapacidad/{disc_id}/editar', [FichaMedicaController::class, 'updateDiscapacidad'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/ficha-medica/{id}/discapacidad/{disc_id}/eliminar', [FichaMedicaController::class, 'destroyDiscapacidad'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Reportes
    $r->get('/reportes', [ReportesController::class, 'index']);
    $r->get('/reportes/atletas/listado', [ReportesController::class, 'listaAtletas']);
    $r->get('/reportes/atleta/{id}', [ReportesController::class, 'fichaAtleta']);
    $r->get('/reportes/usuarios/listado', [ReportesController::class, 'listaUsuarios']);
    $r->get('/reportes/usuario/{id}', [ReportesController::class, 'fichaUsuario']);
    $r->get('/reportes/asistencia/atleta/{id}', [ReportesController::class, 'asistenciaAtleta']);
    $r->get('/reportes/asistencia/categoria', [ReportesController::class, 'asistenciaCategoria']);
    $r->get('/reportes/categoria/{id}', [ReportesController::class, 'categoria']);

    // Mi Perfil (todos los usuarios autenticados)
    $r->get('/perfil', [PerfilController::class, 'index']);
    $r->post('/perfil', [PerfilController::class, 'updatePerfil'], [CsrfMiddleware::class]);
    $r->post('/perfil/seguridad', [PerfilController::class, 'updateSeguridad'], [CsrfMiddleware::class]);

    // Configuración (sólo admin)
    $r->get('/configuracion', [ConfiguracionController::class, 'index'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/configuracion', [ConfiguracionController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
});

// ---------------------------------------------------------------------------
// API REST (JSON, requiere auth salvo excepciones)
// ---------------------------------------------------------------------------
$router->group('/api', [AuthMiddleware::class], function ($r) {
    // Ubicaciones cascada
    $r->get('/direcciones/paises', [DireccionesApiController::class, 'paises']);
    $r->get('/direcciones/estados/{paisId}', [DireccionesApiController::class, 'estados']);
    $r->get('/direcciones/municipios/{estadoId}', [DireccionesApiController::class, 'municipios']);
    $r->get('/direcciones/parroquias/{municipioId}', [DireccionesApiController::class, 'parroquias']);

    // Atletas (JSON para tablas y selects)
    $r->get('/atletas', [AtletasApiController::class, 'index']);
    $r->get('/atletas/{id}', [AtletasApiController::class, 'show']);

    // Antropometría (datos para gráficos)
    $r->get('/medidas/atleta/{id}', [MedidasAntropometricasApiController::class, 'historial']);

    // Pruebas físicas (datos para radar chart)
    $r->get('/resultados-pruebas/atleta/{id}', [ResultadosPruebasApiController::class, 'historial']);

    // Asistencia (lista atletas por categoría para pase)
    $r->get('/asistencias/categoria/{id}', [AsistenciasApiController::class, 'atletasCategoria']);

    // Reportes (endpoints de datos agregados)
    $r->get('/reportes/resumen', [ReportesApiController::class, 'resumen']);

    // Keep-alive de sesión
    $r->post('/keep-alive', [AuthController::class, 'keepAlive']);
});
