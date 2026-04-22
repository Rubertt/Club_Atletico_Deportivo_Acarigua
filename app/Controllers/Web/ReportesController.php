<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\ReporteService;

final class ReportesController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::connection();
        $stats = [
            'atletas'         => (int) $db->query('SELECT COUNT(*) FROM atletas')->fetchColumn(),
            'activos'         => (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus='Activo'")->fetchColumn(),
            'categorias'      => (int) $db->query("SELECT COUNT(*) FROM categoria WHERE estatus='Activa'")->fetchColumn(),
            'personal'        => (int) $db->query('SELECT COUNT(*) FROM personal')->fetchColumn(),
            'eventos_30dias'  => (int) $db->query("SELECT COUNT(*) FROM actividades WHERE fecha >= (CURDATE() - INTERVAL 30 DAY)")->fetchColumn(),
        ];
        $atletas = $db->query("SELECT atleta_id, nombre, apellido, cedula FROM atletas ORDER BY apellido, nombre")->fetchAll();
        return $this->view('reportes.index', [
            'title' => 'Reportes',
            'active' => 'reportes',
            'breadcrumb' => ['Inicio', 'Reportes'],
            'stats' => $stats,
            'atletas' => $atletas,
        ], 'admin');
    }

    public function fichaAtleta(Request $request): Response
    {
        $id = (int) $request->param('id');
        $reporte = (new ReporteService())->fichaAtleta($id);
        if (!$reporte) {
            return Response::html('<h1>Atleta no encontrado</h1>', 404);
        }
        // Si es PDF, descarga; si es HTML, renderiza inline (permite imprimir)
        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function asistencia(Request $request): Response
    {
        return $this->view('reportes.index', [
            'title' => 'Reporte de asistencia',
            'active' => 'reportes',
            'breadcrumb' => ['Inicio', 'Reportes', 'Asistencia'],
            'stats' => [],
            'atletas' => [],
        ], 'admin');
    }

    public function categoria(Request $request): Response
    {
        return $this->view('reportes.index', [
            'title' => 'Reporte por categoría',
            'active' => 'reportes',
            'breadcrumb' => ['Inicio', 'Reportes', 'Categoría'],
            'stats' => [],
            'atletas' => [],
        ], 'admin');
    }
}
