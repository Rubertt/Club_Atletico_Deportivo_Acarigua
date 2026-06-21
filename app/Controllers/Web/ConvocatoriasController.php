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
use App\Models\Atleta;
use App\Models\Categoria;
use App\Models\Usuario;
use App\Models\Convocatoria;
use Throwable;

final class ConvocatoriasController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'usuario_id' => $request->query('usuario_id'),
            'categoria_id' => $request->query('categoria_id'),
        ];

        $where = ["a.tipo_actividad = 0"];
        $params = [];

        if ($filters['usuario_id'] !== null && $filters['usuario_id'] !== '') {
            $where[] = "a.usuario_id = :usuario_id";
            $params['usuario_id'] = (int) $filters['usuario_id'];
        }

        if ($filters['categoria_id'] !== null && $filters['categoria_id'] !== '') {
            $where[] = "EXISTS (SELECT 1 FROM asig_categorias ac2 WHERE ac2.asignacion_id = a.asignacion_id AND ac2.categoria_id = :categoria_id)";
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }

        $whereClause = implode(" AND ", $where);
        
        $sql = "SELECT a.actividad_id AS evento_id, a.fecha AS fecha_evento, a.ubicacion, a.terreno, a.clima, a.estatus AS actividad_estatus,
                    CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
                    (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria,
                    (SELECT COUNT(*) FROM convocatorias conv WHERE conv.actividad_id = a.actividad_id AND conv.estatus = 1) AS total_convocados,
                    (SELECT COUNT(*) FROM convocatorias conv WHERE conv.actividad_id = a.actividad_id AND conv.estatus = 1) AS convocados_si,
                    (SELECT COUNT(*) FROM convocatorias conv WHERE conv.actividad_id = a.actividad_id AND conv.asistencia = 3) AS asistieron
            FROM actividades a
            LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
            WHERE {$whereClause}
            ORDER BY a.fecha DESC, a.actividad_id DESC
            LIMIT 50";

        $db = Database::connection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $eventos = $stmt->fetchAll();

        $categorias = (new Categoria())->activas();
        $enlistadores = (new Usuario())->query(
            "SELECT usuario_id, nombre, apellido FROM usuarios 
            WHERE estatus = 'Activo' AND rol_id IN (" . ROL_ADMIN . ", " . ROL_ENTRENADOR . ", " . ROL_DIRECTIVO . ") 
            ORDER BY apellido, nombre"
        );

        $viewData = [
            'title' => 'Convocatorias',
            'active' => 'convocatorias',
            'breadcrumb' => ['Inicio', 'Convocatorias'],
            'eventos' => $eventos,
            'filters' => $filters,
            'categorias' => $categorias,
            'enlistadores' => $enlistadores,
        ];

        if ($request->query('ajax') || $request->input('ajax')) {
            return Response::html($this->renderView('convocatorias.index', $viewData));
        }

        return $this->view('convocatorias.index', $viewData, 'admin');
    }

    public function crear(Request $request): Response
    {
        $categorias = (new Categoria())->activas();
        return $this->view('convocatorias.crear', [
            'title' => 'Convocatorias',
            'active' => 'convocatorias',
            'breadcrumb' => ['Inicio', 'Convocatorias', 'Programar'],
            'categorias' => $categorias,
        ], 'admin');
    }

    public function guardar(Request $request): Response
    {
        $data = [
            'fecha_evento' => (string) $request->input('fecha_evento', date('Y-m-d')),
            'entrenador_id' => (int) Auth::id(),
            'categoria_id' => (int) $request->input('categoria_id', 0),
            'ubicacion' => trim((string) $request->input('ubicacion', '')),
            'terreno' => $request->input('terreno') !== '' ? (int)$request->input('terreno') : null,
            'clima' => $request->input('clima') !== '' ? (int) $request->input('clima') : null,
            'hora_inicio' => $request->input('hora_inicio') ?: null,
            'hora_fin' => $request->input('hora_fin') ?: null,
        ];

        $v = Validator::make($data, [
            'fecha_evento' => 'required|date',
            'categoria_id' => 'required|integer',
            'hora_inicio'  => 'required',
            'hora_fin'     => 'required',
            'ubicacion'    => 'required',
        ], [
            'hora_inicio'  => 'El campo "Hora inicio" es obligatorio.',
            'hora_fin'     => 'El campo "Hora Fin" es obligatorio.',
            'categoria_id' => 'El campo "Categoría Deportiva" es obligatorio.',
            'ubicacion'    => 'La ubicación es obligatoria.',
        ]);

        if (!empty($data['fecha_evento'])) {
            $minDateStr = date('Y-m-d', strtotime('+1 day'));
            $maxDateStr = date('Y-m-d', strtotime('+3 months'));
            $eventTime = strtotime($data['fecha_evento']);

            if ($eventTime < strtotime($minDateStr)) {
                $v->addError('fecha_evento', 'El campo "Fecha del Partido" es menor al valor mínimo permitido.');
            } elseif ($eventTime > strtotime($maxDateStr)) {
                $v->addError('fecha_evento', 'El campo "Fecha del Partido" es mayor al valor máximo permitido.');
            }
        }

        if (!$v->validate()) {
            $this->withOld($request->body());
            $this->withErrors($v->errors());
            return $this->redirect('/admin/convocatorias/crear');
        }

        if (!empty($data['hora_inicio']) && !empty($data['hora_fin']) && strtotime($data['hora_inicio']) >= strtotime($data['hora_fin'])) {
            $this->withOld($request->body());
            flash('error', 'La hora de inicio debe ser menor a la hora de fin.');
            return $this->redirect('/admin/convocatorias/crear');
        }

        $db = Database::connection();

        // 1. Obtener asignacion_id de la categoría
        $stmtAsig = $db->prepare("SELECT asignacion_id FROM asig_categorias WHERE categoria_id = ? AND estatus = 1 LIMIT 1");
        $stmtAsig->execute([$data['categoria_id']]);
        $asignacionId = $stmtAsig->fetchColumn();
        $asignacionId = $asignacionId ? (int)$asignacionId : null;
        if (!$asignacionId) {
            $stmtAsig = $db->prepare("SELECT asignacion_id FROM asig_categorias WHERE categoria_id = ? LIMIT 1");
            $stmtAsig->execute([$data['categoria_id']]);
            $asignacionId = $stmtAsig->fetchColumn();
            $asignacionId = $asignacionId ? (int)$asignacionId : null;
        }

        // 2. Validar duplicado de Partido/Entrenamiento en esa fecha
        $stmt = $db->prepare("
            SELECT a.actividad_id, a.tipo_actividad
            FROM actividades a
            LEFT JOIN asig_categorias ac ON a.asignacion_id = ac.asignacion_id
            LEFT JOIN asistencias ast ON a.actividad_id = ast.actividad_id
            LEFT JOIN asig_categorias ac2 ON ast.atleta_id = ac2.atleta_id
            WHERE a.fecha = ? AND a.tipo_actividad IN (0, 1) AND (ac.categoria_id = ? OR ac2.categoria_id = ?)
            LIMIT 1
        ");
        $stmt->execute([$data['fecha_evento'], $data['categoria_id'], $data['categoria_id']]);
        $existing = $stmt->fetch();
        if ($existing) {
            $existenteTipo = (int)$existing['tipo_actividad'] === 0 ? 'Partido' : 'Entrenamiento';
            flash('error', "No se puede registrar la convocatoria porque ya existe un $existenteTipo registrado para esta categoría en la misma fecha.");
            $this->withOld($request->body());
            return $this->redirect('/admin/convocatorias/crear');
        }

        // 3. Procesar atletas y estatus
        $atletaIds = (array) ($request->body('atletas') ?? []);
        $estatuses = (array) ($request->body('estatus') ?? []);
        $convocadosData = [];

        foreach ($atletaIds as $aid) {
            $aid = (int) $aid;
            if (!$aid) continue;

            $atletaObj = (new Atleta())->findCompleto($aid);
            if ($atletaObj && in_array((int)$atletaObj['estatus'], [0, 3], true)) {
                flash('error', 'No es posible registrar convocatorias para atletas inactivos o suspendidos.');
                $this->withOld($request->body());
                return $this->redirect('/admin/convocatorias/crear');
            }

            // Estatus: 1 = Convocado, 2 = No Convocado
            $est = (int) ($estatuses[$aid] ?? 2);
            if ($est !== 1 && $est !== 2) {
                $est = 2;
            }

            $convocadosData[] = [
                'atleta_id' => $aid,
                'estatus' => $est
            ];
        }

        if (empty($convocadosData)) {
            flash('error', 'Debes incluir al menos un atleta.');
            $this->withOld($request->body());
            return $this->redirect('/admin/convocatorias/crear');
        }

        try {
            $actividadData = [
                'usuario_id'     => $data['entrenador_id'],
                'tipo_actividad' => 0, // Partido
                'asignacion_id'  => $asignacionId,
                'fecha'          => $data['fecha_evento'],
                'hora_inicio'    => $data['hora_inicio'],
                'hora_fin'       => $data['hora_fin'],
                'ubicacion'      => $data['ubicacion'],
                'terreno'        => $data['terreno'],
                'clima'          => $data['clima'],
                'estatus'        => 1, // Programado
            ];

            (new Convocatoria())->registrarLote($actividadData, $convocadosData);

            flash('success', 'Convocatoria registrada correctamente.');
            return $this->redirect('/admin/convocatorias');
        } catch (Throwable $e) {
            Logger::error($e);
            $this->withOld($request->body());
            flash('error', 'Error al guardar la convocatoria: ' . $e->getMessage());
            return $this->redirect('/admin/convocatorias/crear');
        }
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        $actividad = $db->prepare(
            "SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
            (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria
            FROM actividades a
            LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
            WHERE a.actividad_id = ? AND a.tipo_actividad = 0"
        );
        $actividad->execute([$id]);
        $actividad = $actividad->fetch();

        if (!$actividad) {
            flash('error', 'Registro no encontrado.');
            return $this->redirect('/admin/convocatorias');
        }

        $convocatorias = $db->prepare(
            "SELECT conv.*, atl.nombre, atl.apellido, atl.cedula, atl.foto, ac.nun_dorsal
            FROM convocatorias conv
            JOIN atletas atl ON conv.atleta_id = atl.atleta_id
            LEFT JOIN asig_categorias ac ON ac.atleta_id = conv.atleta_id 
                AND ac.categoria_id = (
                    SELECT ac2.categoria_id 
                    FROM asig_categorias ac2 
                    JOIN actividades a ON a.asignacion_id = ac2.asignacion_id
                    WHERE a.actividad_id = conv.actividad_id 
                    LIMIT 1
                )
                AND ac.estatus = 1
            WHERE conv.actividad_id = ?
            ORDER BY atl.apellido, atl.nombre"
        );
        $convocatorias->execute([$id]);
        $detalles = $convocatorias->fetchAll();

        return $this->view('convocatorias.show', [
            'title' => 'Detalle de Convocatoria',
            'active' => 'convocatorias',
            'breadcrumb' => ['Inicio', 'Convocatorias', 'Detalle'],
            'actividad' => $actividad,
            'detalles' => $detalles
        ], 'admin');
    }

    public function imprimir(Request $request): Response
    {
        $id = (int) $request->param('id');
        $reporte = (new \App\Services\ReporteConvocatoriasService())->reporteDetalle($id);
        if (!$reporte) {
            return Response::html('<h1>Convocatoria no encontrada</h1>', 404);
        }
        if (str_starts_with($reporte['mime'], 'application/pdf')) {
            if ($request->query('action') === 'download') {
                return Response::download($reporte['content'], $reporte['filename'], $reporte['mime']);
            }
            return Response::inline($reporte['content'], $reporte['filename'], $reporte['mime']);
        }
        return Response::html($reporte['content']);
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        $actividad = $db->prepare(
            "SELECT a.*,
            (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria
            FROM actividades a
            WHERE a.actividad_id = ? AND a.tipo_actividad = 0"
        );
        $actividad->execute([$id]);
        $actividad = $actividad->fetch();

        if (!$actividad) {
            flash('error', 'Registro no encontrado.');
            return $this->redirect('/admin/convocatorias');
        }

        if ((int)$actividad['estatus'] === 2) {
            flash('error', 'No se puede editar una convocatoria finalizada.');
            return $this->redirect('/admin/convocatorias');
        }

        // Restricción de 30 días para entrenadores
        if (Auth::user()['rol_id'] == ROL_ENTRENADOR) {
            $fechaActividad = strtotime($actividad['fecha']);
            $limite = strtotime('+30 days', $fechaActividad);
            if (time() > $limite) {
                flash('error', 'El tiempo permitido para editar esta asistencia de partido (30 días) ha expirado.');
                return $this->redirect('/admin/convocatorias');
            }
        }

        $convocatorias = $db->prepare(
            "SELECT conv.*, atl.nombre, atl.apellido, atl.cedula, atl.foto, ac.nun_dorsal
            FROM convocatorias conv
            JOIN atletas atl ON conv.atleta_id = atl.atleta_id
            LEFT JOIN asig_categorias ac ON ac.atleta_id = conv.atleta_id 
                AND ac.categoria_id = (
                    SELECT ac2.categoria_id 
                    FROM asig_categorias ac2 
                    JOIN actividades a ON a.asignacion_id = ac2.asignacion_id
                    WHERE a.actividad_id = conv.actividad_id 
                    LIMIT 1
                )
                AND ac.estatus = 1
            WHERE conv.actividad_id = ?
            ORDER BY atl.apellido, atl.nombre"
        );
        $convocatorias->execute([$id]);
        $detalles = $convocatorias->fetchAll();

        return $this->view('convocatorias.edit', [
            'title' => 'Asistencia de Partido',
            'active' => 'convocatorias',
            'breadcrumb' => ['Inicio', 'Convocatorias', 'Asistencia de Partido'],
            'actividad' => $actividad,
            'detalles' => $detalles,
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        $act = $db->prepare("SELECT * FROM actividades WHERE actividad_id = ? AND tipo_actividad = 0");
        $act->execute([$id]);
        $actividad = $act->fetch();

        if (!$actividad) {
            flash('error', 'Registro no encontrado.');
            return $this->redirect('/admin/convocatorias');
        }

        if ((int)$actividad['estatus'] === 2) {
            flash('error', 'No se puede actualizar una convocatoria finalizada.');
            return $this->redirect('/admin/convocatorias');
        }

        if (Auth::user()['rol_id'] == ROL_ENTRENADOR) {
            if (time() > strtotime('+30 days', strtotime($actividad['fecha']))) {
                flash('error', 'El tiempo permitido para editar esta asistencia de partido (30 días) ha expirado.');
                return $this->redirect('/admin/convocatorias');
            }
        }

        $data = [
            'fecha_evento' => (string) $request->input('fecha_evento', date('Y-m-d')),
            'entrenador_id' => (int) Auth::id(),
            'ubicacion' => trim((string) $request->input('ubicacion', '')),
            'terreno' => $request->input('terreno') !== '' ? (int)$request->input('terreno') : null,
            'clima' => $request->input('clima') !== '' ? (int) $request->input('clima') : null,
            'hora_inicio' => $request->input('hora_inicio') ?: null,
            'hora_fin' => $request->input('hora_fin') ?: null,
        ];

        $v = Validator::make($data, [
            'fecha_evento' => 'required|date',
            'hora_inicio'  => 'required',
            'hora_fin'     => 'required',
            'ubicacion'    => 'required',
        ], [
            'hora_inicio'  => 'El campo "Hora inicio" es obligatorio.',
            'hora_fin'     => 'El campo "Hora Fin" es obligatorio.',
            'ubicacion'    => 'La ubicación es obligatoria.',
        ]);

        if (!empty($data['fecha_evento'])) {
            $minDateStr = date('Y-m-d', strtotime('+1 day'));
            $maxDateStr = date('Y-m-d', strtotime('+3 months'));
            $eventTime = strtotime($data['fecha_evento']);

            if ($eventTime < strtotime($minDateStr)) {
                $v->addError('fecha_evento', 'El campo "Fecha del Partido" es menor al valor mínimo permitido.');
            } elseif ($eventTime > strtotime($maxDateStr)) {
                $v->addError('fecha_evento', 'El campo "Fecha del Partido" es mayor al valor máximo permitido.');
            }
        }

        if (!$v->validate()) {
            $this->withErrors($v->errors());
            return $this->redirect("/admin/convocatorias/{$id}/editar");
        }

        if (!empty($data['hora_inicio']) && !empty($data['hora_fin']) && strtotime($data['hora_inicio']) >= strtotime($data['hora_fin'])) {
            flash('error', 'La hora de inicio debe ser menor a la hora de fin.');
            return $this->redirect("/admin/convocatorias/{$id}/editar");
        }

        $estatuses = (array) ($request->body('estatus') ?? []);
        $asistencias = (array) ($request->body('asistencia') ?? []);
        $atletas = (array) ($request->body('atletas') ?? []);
        $convocadosData = [];

        foreach ($atletas as $aid) {
            $aid = (int) $aid;
            if (!$aid) continue;

            $est = (int) ($estatuses[$aid] ?? 2);
            if ($est !== 1 && $est !== 2) {
                $est = 2;
            }

            if ($est === 1) {
                $asis = (int) ($asistencias[$aid] ?? 4); // Default to 4 (no asistió)
                if ($asis !== 3 && $asis !== 4) {
                    $asis = 4;
                }
            } else {
                $asis = null;
            }

            $convocadosData[] = [
                'atleta_id'  => $aid,
                'estatus'    => $est,
                'asistencia' => $asis
            ];
        }

        try {
            $actividadData = [
                'usuario_id'     => $data['entrenador_id'],
                'fecha'          => $data['fecha_evento'],
                'hora_inicio'    => $data['hora_inicio'],
                'hora_fin'       => $data['hora_fin'],
                'ubicacion'      => $data['ubicacion'],
                'terreno'        => $data['terreno'],
                'clima'          => $data['clima'],
                'estatus'        => 2, // Finalizado (asistencia tomada)
            ];

            (new Convocatoria())->actualizarLote($id, $actividadData, $convocadosData);

            flash('success', 'Asistencia de partido actualizada correctamente.');
            return $this->redirect('/admin/convocatorias');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'Error al guardar la asistencia de partido: ' . $e->getMessage());
            return $this->redirect("/admin/convocatorias/{$id}/editar");
        }
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        $act = $db->prepare("SELECT * FROM actividades WHERE actividad_id = ? AND tipo_actividad = 0");
        $act->execute([$id]);
        $actividad = $act->fetch();

        if (!$actividad) {
            flash('error', 'Registro no encontrado.');
            return $this->redirect('/admin/convocatorias');
        }

        if ((int)$actividad['estatus'] === 2) {
            flash('error', 'No se puede eliminar una convocatoria finalizada.');
            return $this->redirect('/admin/convocatorias');
        }

        if ($actividad['fecha'] < date('Y-m-d')) {
            flash('error', 'No se puede eliminar una convocatoria de un partido que ya se ha jugado.');
            return $this->redirect('/admin/convocatorias');
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("DELETE FROM convocatorias WHERE actividad_id = ?");
            $stmt->execute([$id]);

            $stmt = $db->prepare("DELETE FROM actividades WHERE actividad_id = ?");
            $stmt->execute([$id]);

            $db->commit();
            flash('success', 'Convocatoria eliminada correctamente.');
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Logger::error($e);
            flash('error', 'No se pudo eliminar el registro.');
        }

        return $this->redirect('/admin/convocatorias');
    }
}
