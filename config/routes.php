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
use App\Controllers\Web\ConvocatoriasController;
use App\Controllers\Web\MedidasAntropometricasController;
use App\Controllers\Web\ResultadosPruebasController;
use App\Controllers\Web\FichaMedicaController;
use App\Controllers\Web\ConsultaMedicaController;
use App\Controllers\Web\ReportesController;
use App\Controllers\Web\ConfiguracionController;
use App\Controllers\Api\DireccionesApiController;
use App\Controllers\Api\AtletasApiController;
use App\Controllers\Api\MedidasAntropometricasApiController;
use App\Controllers\Api\ResultadosPruebasApiController;
use App\Controllers\Api\AsistenciasApiController;
use App\Controllers\Api\ConvocatoriasApiController;
use App\Controllers\Api\ReportesApiController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\MedicoMiddleware;

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
    $r->get('/categorias', [CategoriasController::class, 'index'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/categorias/crear', [CategoriasController::class, 'create'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias', [CategoriasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/categorias/{id}/editar', [CategoriasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias/{id}', [CategoriasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias/{id}/eliminar', [CategoriasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Asignaciones de Categorías
    $r->get('/categorias/{id}/detalles', [\App\Controllers\Web\AsigCategoriasController::class, 'index'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/categorias/{id}/asignar', [\App\Controllers\Web\AsigCategoriasController::class, 'create'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/categorias/{id}/asignar', [\App\Controllers\Web\AsigCategoriasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/asig-categorias/{id}/editar', [\App\Controllers\Web\AsigCategoriasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asig-categorias/{id}/editar', [\App\Controllers\Web\AsigCategoriasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asig-categorias/{id}/eliminar', [\App\Controllers\Web\AsigCategoriasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Usuarios (sólo admin)
    $r->get('/usuarios', [UsuariosController::class, 'index'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/usuarios/crear', [UsuariosController::class, 'create'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios', [UsuariosController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/validar-paso', [UsuariosController::class, 'validarPaso'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/usuarios/{id}/perfil', [UsuariosController::class, 'show'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->get('/usuarios/{id}/editar', [UsuariosController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}', [UsuariosController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/update-basico', [UsuariosController::class, 'updateBasico'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/foto', [UsuariosController::class, 'updateFoto'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/direccion', [UsuariosController::class, 'updateDireccion'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/eliminar', [UsuariosController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/usuarios/{id}/restablecer', [UsuariosController::class, 'restablecer'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Asistencias (admin + entrenador)
    $r->get('/asistencias', [AsistenciasController::class, 'index'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/asistencias/crear', [AsistenciasController::class, 'crear'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->post('/asistencias/crear', [AsistenciasController::class, 'guardar'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/asistencias/{id}', [AsistenciasController::class, 'show'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/asistencias/{id}/editar', [AsistenciasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asistencias/{id}/editar', [AsistenciasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);
    $r->post('/asistencias/{id}/eliminar', [AsistenciasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user']]]);

    // Convocatorias (admin + entrenador)
    $r->get('/convocatorias', [ConvocatoriasController::class, 'index'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/convocatorias/crear', [ConvocatoriasController::class, 'crear'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->post('/convocatorias/crear', [ConvocatoriasController::class, 'guardar'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/convocatorias/{id}', [ConvocatoriasController::class, 'show'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/convocatorias/{id}/imprimir', [ConvocatoriasController::class, 'imprimir'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/convocatorias/{id}/editar', [ConvocatoriasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/convocatorias/{id}/editar', [ConvocatoriasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/convocatorias/{id}/eliminar', [ConvocatoriasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);

    // Medidas Antropometricas
    $r->get('/medidas', [MedidasAntropometricasController::class, 'index'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/medidas/atleta/{id}', [MedidasAntropometricasController::class, 'atleta'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->post('/medidas/atleta/{id}', [MedidasAntropometricasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->post('/medidas/{id}/eliminar', [MedidasAntropometricasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/medidas/{id}/editar', [MedidasAntropometricasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);

    // Pruebas físicas (CRUD Masivo)
    $r->get('/resultados-pruebas', [ResultadosPruebasController::class, 'index'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/resultados-pruebas/crear', [ResultadosPruebasController::class, 'crear'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->post('/resultados-pruebas/crear', [ResultadosPruebasController::class, 'guardar'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/resultados-pruebas/sesion/{id}', [ResultadosPruebasController::class, 'show'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/resultados-pruebas/sesion/{id}/editar', [ResultadosPruebasController::class, 'edit'], [[RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/resultados-pruebas/sesion/{id}/editar', [ResultadosPruebasController::class, 'actualizar'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/resultados-pruebas/sesion/{id}/eliminar', [ResultadosPruebasController::class, 'eliminarSesion'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);

    // Rutas legacy de pruebas físicas por atleta individual
    $r->get('/resultados-pruebas/atleta/{id}', [ResultadosPruebasController::class, 'atleta'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->post('/resultados-pruebas/atleta/{id}', [ResultadosPruebasController::class, 'store'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->post('/resultados-pruebas/{id}/eliminar', [ResultadosPruebasController::class, 'destroy'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);
    $r->post('/resultados-pruebas/{id}/editar', [ResultadosPruebasController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'entrenador']]]);

    // Ficha médica (lectura entrenador; escritura admin/medico)
    $r->get('/ficha-medica/{id}', [FichaMedicaController::class, 'show']);
    $r->post('/ficha-medica/{id}', [FichaMedicaController::class, 'update'], [CsrfMiddleware::class, MedicoMiddleware::class]);
    $r->post('/ficha-medica/{id}/discapacidad', [FichaMedicaController::class, 'storeDiscapacidad'], [CsrfMiddleware::class, MedicoMiddleware::class]);
    $r->post('/ficha-medica/{id}/discapacidad/{disc_id}/editar', [FichaMedicaController::class, 'updateDiscapacidad'], [CsrfMiddleware::class, MedicoMiddleware::class]);
    $r->post('/ficha-medica/{id}/discapacidad/{disc_id}/eliminar', [FichaMedicaController::class, 'destroyDiscapacidad'], [CsrfMiddleware::class, MedicoMiddleware::class]);

    // Consulta médica (CRUD)
    $r->post('/atletas/{id}/consultas-medicas', [ConsultaMedicaController::class, 'store'], [CsrfMiddleware::class, MedicoMiddleware::class]);
    $r->post('/atletas/{id}/consultas-medicas/{consulta_id}/editar', [ConsultaMedicaController::class, 'update'], [CsrfMiddleware::class, MedicoMiddleware::class]);
    $r->post('/atletas/{id}/consultas-medicas/{consulta_id}/eliminar', [ConsultaMedicaController::class, 'destroy'], [CsrfMiddleware::class, MedicoMiddleware::class]);

    // Reportes
    $r->get('/reportes', [ReportesController::class, 'index'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/reportes/atletas/listado', [ReportesController::class, 'listaAtletas'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/reportes/atleta/{id}', [ReportesController::class, 'fichaAtleta'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/reportes/usuarios/listado', [ReportesController::class, 'listaUsuarios'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/reportes/usuario/{id}', [ReportesController::class, 'fichaUsuario'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/reportes/asistencia/atleta/{id}', [ReportesController::class, 'asistenciaAtleta'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/reportes/asistencia/categoria', [ReportesController::class, 'asistenciaCategoria'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);
    $r->get('/reportes/categoria/{id}', [ReportesController::class, 'categoria'], [[RoleMiddleware::class, ['admin', 'entrenador']]]);

    // Mi Perfil (todos los usuarios autenticados)
    $r->get('/perfil', [PerfilController::class, 'index']);
    $r->post('/perfil', [PerfilController::class, 'updatePerfil'], [CsrfMiddleware::class]);
    $r->post('/perfil/seguridad', [PerfilController::class, 'updateSeguridad'], [CsrfMiddleware::class]);
    $r->get('/manual', [PerfilController::class, 'descargarManual']);

    // Configuración (admin + super_user + medico)
    $r->get('/configuracion', [ConfiguracionController::class, 'index'], [[RoleMiddleware::class, ['admin', 'super_user', 'medico']]]);
    $r->post('/configuracion', [ConfiguracionController::class, 'update'], [CsrfMiddleware::class, [RoleMiddleware::class, ['admin', 'super_user', 'medico']]]);
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

    // Convocatoria (lista atletas por categoría con estadísticas)
    $r->get('/convocatorias/categoria/{id}', [ConvocatoriasApiController::class, 'atletasCategoriaConvocatoria']);

    // Reportes (endpoints de datos agregados)
    $r->get('/reportes/resumen', [ReportesApiController::class, 'resumen']);

    // Keep-alive de sesión
    $r->post('/keep-alive', [AuthController::class, 'keepAlive']);
});
