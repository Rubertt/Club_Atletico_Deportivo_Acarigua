<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Categoria;
use App\Models\Personal;
use App\Services\AsistenciaService;
use Throwable;

final class AsistenciasController extends Controller
{
    public function index(Request $request): Response
    {
        $hoy = date('Y-m-d');
        $eventos = Database::connection()->query(
            "SELECT a.actividad_id AS evento_id, a.tipo_actividad, a.fecha AS fecha_evento,
                    (SELECT COUNT(*) FROM asistencias ast WHERE ast.actividad_id = a.actividad_id) AS total,
                    (SELECT COUNT(*) FROM asistencias ast WHERE ast.actividad_id = a.actividad_id AND ast.estatus = 1) AS presentes
             FROM actividades a
             ORDER BY a.fecha DESC, a.actividad_id DESC
             LIMIT 50"
        )->fetchAll();

        return $this->view('asistencias.index', [
            'title' => 'Asistencia',
            'active' => 'asistencias',
            'breadcrumb' => ['Inicio', 'Pase de Lista'],
            'eventos' => $eventos,
            'hoy' => $hoy,
        ], 'admin');
    }

    public function pase(Request $request): Response
    {
        $categorias = (new Categoria())->activas();
        $entrenadores = (new Personal())->entrenadores();
        return $this->view('asistencias.pase_lista', [
            'title' => 'Pase de lista',
            'active' => 'asistencias',
            'breadcrumb' => ['Inicio', 'Evaluaciones', 'Pase de lista'],
            'categorias' => $categorias,
            'entrenadores' => $entrenadores,
        ], 'admin');
    }

    public function guardarPase(Request $request): Response
    {
        $data = [
            'tipo_evento'   => $request->input('tipo_evento', 'Entrenamiento'),
            'fecha_evento'  => (string) $request->input('fecha_evento', date('Y-m-d')),
            'entrenador_id' => (int) $request->input('entrenador_id', 0),
        ];
        $v = Validator::make($data, [
            'tipo_evento'   => 'required|in:Entrenamiento,Partido,Pruebas,Evento especial',
            'fecha_evento'  => 'required|date',
            'entrenador_id' => 'required|integer',
        ]);
        if (!$v->validate()) {
            $this->withErrors($v->errors());
            return $this->redirect('/admin/asistencias/pase');
        }

        $atletaIds = (array) ($request->body('atletas') ?? []);
        $estatuses = (array) ($request->body('estatus') ?? []);
        $observaciones = (array) ($request->body('observaciones') ?? []);
        $detalles = [];
        foreach ($atletaIds as $aid) {
            $aid = (int) $aid;
            if (!$aid) continue;
            $detalles[] = [
                'atleta_id' => $aid,
                'estatus' => $estatuses[$aid] ?? 'Ausente',
                'observaciones' => $observaciones[$aid] ?? null,
            ];
        }

        try {
            (new AsistenciaService())->registrarPase(
                $data['entrenador_id'],
                $data['tipo_evento'],
                $data['fecha_evento'],
                $detalles
            );
            flash('success', 'Asistencia registrada correctamente.');
            return $this->redirect('/admin/asistencias');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'Error al guardar: ' . $e->getMessage());
            return $this->redirect('/admin/asistencias/pase');
        }
    }
}
