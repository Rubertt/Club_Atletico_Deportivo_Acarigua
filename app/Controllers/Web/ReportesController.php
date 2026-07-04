<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\ReporteAtletaService;
use App\Services\ReporteUsuarioService;
use App\Services\ReporteAsistenciaService;

final class ReportesController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::connection();
        $stats = [
            'atletas'         => (int) $db->query('SELECT COUNT(*) FROM atletas')->fetchColumn(),
            'activos'         => (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus='1'")->fetchColumn(),
            'categorias'      => (int) $db->query("SELECT COUNT(*) FROM categorias WHERE estatus=1")->fetchColumn(),
            'usuarios'        => (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
            'eventos_30dias'  => (int) $db->query("SELECT COUNT(*) FROM actividades WHERE fecha >= (CURDATE() - INTERVAL 30 DAY)")->fetchColumn(),
        ];
        $atletas = $db->query("SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.estatus, ac.categoria_id, c.nombre_categoria 
                               FROM atletas a 
                               LEFT JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id
                               LEFT JOIN categorias c ON c.categoria_id = ac.categoria_id 
                               ORDER BY a.apellido, a.nombre")->fetchAll();
        $usuarios = [];
        if (can('admin')) {
            $usuarios = $db->query("SELECT usuario_id, nombre, apellido, cedula, rol_id, estatus, foto FROM usuarios ORDER BY apellido, nombre")->fetchAll();
        }
        $categoriasList = $db->query("SELECT categoria_id, nombre_categoria FROM categorias WHERE estatus=1 ORDER BY nombre_categoria")->fetchAll();
        return $this->view('reportes.index', [
            'title' => 'Centro de Reportes',
            'active' => 'reportes',
            'breadcrumb' => ['Inicio', 'Reportes'],
            'stats' => $stats,
            'atletas' => $atletas,
            'usuarios' => $usuarios,
            'categorias' => $categoriasList,
        ], 'admin');
    }

    public function fichaAtleta(Request $request): Response
    {
        $id = (int) $request->param('id');
        $reporte = (new ReporteAtletaService())->fichaAtleta($id);
        if (!$reporte) {
            return Response::html('<h1>Atleta no encontrado</h1>', 404);
        }
        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function categoria(Request $request): Response
    {
        $id = (int) $request->param('id');
        if ($id <= 0) {
            return $this->index($request);
        }

        $reporte = (new ReporteAtletaService())->reportePorCategoria($id);
        if (!$reporte) {
            return Response::html('<h1>Categoría no encontrada</h1>', 404);
        }

        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function listaAtletas(Request $request): Response
    {
        $reporte = (new ReporteAtletaService())->listaAtletas();
        
        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function listaUsuarios(Request $request): Response
    {
        $reporte = (new ReporteUsuarioService())->listaUsuarios();
        
        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function fichaUsuario(Request $request): Response
    {
        $id = (int) $request->param('id');
        $reporte = (new ReporteUsuarioService())->fichaUsuario($id);
        if (!$reporte) {
            return Response::html('<h1>Usuario no encontrado</h1>', 404);
        }
        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function asistenciaAtleta(Request $request): Response
    {
        $id = (int) $request->param('id');
        $desde = $request->query('desde') ?: null;
        $hasta = $request->query('hasta') ?: null;

        $reporte = (new ReporteAsistenciaService())->asistenciaIndividual($id, $desde, $hasta);
        if (!$reporte) {
            return Response::html('<h1>Atleta no encontrado</h1>', 404);
        }
        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function asistenciaCategoria(Request $request): Response
    {
        $categoriaId = (int) $request->query('categoria');
        $desde = $request->query('desde') ?: null;
        $hasta = $request->query('hasta') ?: null;

        if ($categoriaId <= 0) {
            return Response::html('<h1>Categoría no válida</h1>', 400);
        }

        $reporte = (new ReporteAsistenciaService())->asistenciaCategoria($categoriaId, $desde, $hasta);
        if (!$reporte) {
            return Response::html('<h1>Categoría no encontrada</h1>', 404);
        }

        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function obtenerActividadesPorCategoria(Request $request): Response
    {
        $categoriaId = (int) $request->query('categoria_id');
        if ($categoriaId <= 0) {
            return Response::json([]);
        }

        $db = Database::connection();
        $stmt = $db->prepare("
            SELECT DISTINCT a.actividad_id, a.fecha,
                   (SELECT COUNT(*) FROM resultados_pruebas rp WHERE rp.actividad_id = a.actividad_id) AS total,
                   c.nombre_categoria
            FROM actividades a
            JOIN asig_categorias ac ON ac.asignacion_id = a.asignacion_id
            JOIN categorias c ON c.categoria_id = ac.categoria_id
            WHERE a.tipo_actividad = 2 AND ac.categoria_id = ?
            ORDER BY a.fecha DESC, a.actividad_id DESC
        ");
        $stmt->execute([$categoriaId]);
        $actividades = $stmt->fetchAll();

        return Response::json($actividades);
    }

    public function pruebasCategoria(Request $request): Response
    {
        $categoriaId = (int) $request->param('categoria_id');
        $actividadId = (int) $request->param('actividad_id');

        if ($categoriaId <= 0 || $actividadId <= 0) {
            return Response::html('<h1>Parámetros inválidos</h1>', 400);
        }

        $reporte = (new \App\Services\ReportePruebasFisicasService())->reporteCategoria($categoriaId, $actividadId);
        if (!$reporte) {
            return Response::html('<h1>Categoría o sesión no encontrada</h1>', 404);
        }

        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }
}
