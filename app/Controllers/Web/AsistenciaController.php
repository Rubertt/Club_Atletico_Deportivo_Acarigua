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
use App\Models\Plantel;
use App\Services\AsistenciaService;
use Throwable;

final class AsistenciaController extends Controller
{
    public function index(Request $request): Response
    {
        $hoy = date('Y-m-d');
        $eventos = Database::connection()->query(
            "SELECT ev.evento_id, ev.tipo_evento, ev.fecha_evento,
                    CONCAT_WS(' ', p.nombre, p.apellido) AS entrenador,
                    (SELECT COUNT(*) FROM detalle_asistencia da WHERE da.evento_id = ev.evento_id) AS total,
                    (SELECT COUNT(*) FROM detalle_asistencia da WHERE da.evento_id = ev.evento_id AND da.estatus='Presente') AS presentes
             FROM evento_deportivo ev
             JOIN plantel p ON p.plantel_id = ev.entrenador_id
             ORDER BY ev.fecha_evento DESC, ev.evento_id DESC
             LIMIT 50"
        )->fetchAll();

        return $this->view('asistencia.index', [
            'title' => 'Asistencia',
            'active' => 'asistencia',
            'breadcrumb' => ['Inicio', 'Asistencia'],
            'eventos' => $eventos,
            'hoy' => $hoy,
        ], 'admin');
    }

    public function pase(Request $request): Response
    {
        $categorias = (new Categoria())->activas();
        $entrenadores = (new Plantel())->entrenadores();
        return $this->view('asistencia.pase_lista', [
            'title' => 'Pase de lista',
            'active' => 'asistencia',
            'breadcrumb' => ['Inicio', 'Asistencia', 'Pase de lista'],
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
            return $this->redirect('/admin/asistencia/pase');
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
            return $this->redirect('/admin/asistencia');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'Error al guardar: ' . $e->getMessage());
            return $this->redirect('/admin/asistencia/pase');
        }
    }
}
