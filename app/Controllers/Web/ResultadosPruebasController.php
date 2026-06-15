<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Atleta;
use App\Models\Categoria;
use App\Models\ResultadoPrueba;

final class ResultadosPruebasController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'usuario_id' => $request->query('usuario_id'),
            'categoria_id' => $request->query('categoria_id'),
        ];

        $where = ["a.tipo_actividad = 2"];
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
        
        $sql = "SELECT a.actividad_id AS evento_id, a.tipo_actividad AS tipo_evento, a.fecha AS fecha_evento,
                    CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
                    (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria,
                    (SELECT COUNT(*) FROM resultados_pruebas rp WHERE rp.actividad_id = a.actividad_id) AS total
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
        $enlistadores = (new \App\Models\Usuario())->query(
            "SELECT usuario_id, nombre, apellido FROM usuarios 
             WHERE estatus = 'Activo' AND rol_id IN (" . ROL_ADMIN . ", " . ROL_ENTRENADOR . ", " . ROL_DIRECTIVO . ") 
             ORDER BY apellido, nombre"
        );

        $viewData = [
            'title' => 'Pruebas físicas',
            'active' => 'resultados_pruebas',
            'breadcrumb' => ['Inicio', 'Pruebas físicas'],
            'eventos' => $eventos,
            'filters' => $filters,
            'categorias' => $categorias,
            'enlistadores' => $enlistadores,
        ];

        if ($request->query('ajax') || $request->input('ajax')) {
            return Response::html($this->renderView('resultados_pruebas.index', $viewData));
        }

        return $this->view('resultados_pruebas.index', $viewData, 'admin');
    }

    public function atleta(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) { flash('error', 'No encontrado.'); return $this->redirect('/admin/pruebas'); }
        return $this->view('resultados_pruebas.atleta', [
            'title' => 'Pruebas - ' . $atleta['nombre'],
            'active' => 'pruebas',
            'breadcrumb' => [
                'Inicio',
                ['label' => 'Reportes', 'url' => url('/admin/reportes')],
                ['label' => 'Pruebas físicas', 'url' => url('/admin/reportes/pruebas-fisicas')],
                $atleta['nombre'] . ' ' . $atleta['apellido']
            ],
            'atleta' => $atleta,
            'historial' => (new ResultadoPrueba())->historial($id),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/pruebas');
        }

        if (in_array((int)$atleta['estatus'], [0, 3], true)) {
            $msg = 'No es posible registrar pruebas físicas para un atleta suspendido o inactivo.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 403);
            }
            flash('error', $msg);
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        }

        $fechaEvaluacion = (string) $request->input('fecha_evaluacion', date('Y-m-d'));

        // Validar que la fecha de evaluación no sea futura
        if (strtotime($fechaEvaluacion) > strtotime(date('Y-m-d'))) {
            $msg = 'La fecha de evaluación no puede ser en el futuro.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        }

        $db = Database::connection();
        $entrenadorId = (int) $request->input('entrenador_id');
        if (!$entrenadorId) {
            $entrenadorId = (int) $db->query("SELECT usuario_id FROM usuarios WHERE rol_id = " . ROL_ENTRENADOR . " LIMIT 1")->fetchColumn();
        }

        $eventoId = 0;

        // Buscar si ya existe una actividad de Pruebas Físicas en esa fecha para ese entrenador
        $eventoId = (int) $db->query("SELECT actividad_id FROM actividades WHERE fecha = '$fechaEvaluacion' AND tipo_actividad = 2 AND usuario_id = $entrenadorId LIMIT 1")->fetchColumn();
        
        if (!$eventoId) {
            if ($entrenadorId) {
                $stmt = $db->prepare("INSERT INTO actividades (usuario_id, tipo_actividad, fecha, ubicacion) VALUES (?, 2, ?, ?)");
                $stmt->execute([$entrenadorId, $fechaEvaluacion, 'Cancha Principal']);
                $eventoId = (int) $db->lastInsertId();
            }
        }

        if (!$eventoId) {
            $msg = 'No se pudo determinar un evento o entrenador para registrar la prueba.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $msg], 400);
            }
            flash('error', $msg);
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        }

        // Validar que no exista otra prueba física registrada para este atleta en la fecha seleccionada
        $exists = (int) $db->query("SELECT COUNT(*) FROM resultados_pruebas rp 
                                    INNER JOIN actividades a ON rp.actividad_id = a.actividad_id 
                                    WHERE rp.atleta_id = $id AND DATE(a.fecha) = '$fechaEvaluacion'")->fetchColumn();
        if ($exists > 0) {
            $msg = 'Ya existe un resultado de prueba física registrado para este atleta en la fecha seleccionada (' . date('d/m/Y', strtotime($fechaEvaluacion)) . '). Por favor, edita el registro existente.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $msg], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        }

        $data = [
            'actividad_id'      => $eventoId,
            'atleta_id'         => $id,
            'test_de_fuerza'    => $this->num($request->input('test_de_fuerza')),
            'test_resistencia'  => $this->num($request->input('test_resistencia')),
            'test_velocidad'    => $this->num($request->input('test_velocidad')),
            'test_coordinacion' => $this->num($request->input('test_coordinacion')),
            'test_de_reaccion'  => $this->num($request->input('test_de_reaccion')),
        ];

        $v = \App\Core\Validator::make($data, [
            'test_de_fuerza'    => 'numeric|min:1|max:100',
            'test_resistencia'  => 'numeric|min:1|max:2000',
            'test_velocidad'    => 'numeric|min:1|max:10',
            'test_coordinacion' => 'numeric|min:1|max:200',
            'test_de_reaccion'  => 'numeric|min:10|max:1000',
        ], [
            'test_de_fuerza'    => 'El salto CMJ (Fuerza) debe ser un número entre 1 y 100 cm.',
            'test_resistencia'  => 'El Yo-Yo Test (Resistencia) debe ser un número entre 1 y 10000 metros.',
            'test_velocidad'    => 'El Sprint 30m (Velocidad) debe ser un número entre 1.00 y 10.00 segundos.',
            'test_coordinacion' => 'El Circuito de Conos (Coordinación) debe ser un número entre 1 y 100 segundos.',
            'test_de_reaccion'  => 'La App Cognitiva (Reacción) debe ser un número entre 100 y 1000 ms.',
        ]);

        if (!$v->validate()) {
            $firstError = array_values($v->errors())[0];
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $firstError, 'errors' => $v->errors()], 422);
            }
            flash('error', $firstError);
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        }

        // Validar que no todos los campos estén nulos
        if ($data['test_de_fuerza'] === null && 
            $data['test_resistencia'] === null && 
            $data['test_velocidad'] === null && 
            $data['test_coordinacion'] === null && 
            $data['test_de_reaccion'] === null) {
            
            $msg = 'Debes ingresar al menos el resultado de una prueba física para guardar el registro.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $msg], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        }

        try {
            (new ResultadoPrueba())->insert($data);
            
            if ($request->isAjax() || $request->isJson()) {
                flash('success', 'Prueba física registrada correctamente.');
                return Response::json(['success' => true, 'message' => 'Prueba física registrada correctamente.']);
            }
            
            flash('success', 'Prueba registrada.');
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()], 500);
            }
            flash('error', 'No se pudo registrar: ' . $e->getMessage());
            return $this->redirect("/admin/resultados-pruebas/atleta/$id");
        }
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $prueba = (new ResultadoPrueba())->find($id);

        if (!$prueba) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Prueba no encontrada.'], 404);
            }
            flash('error', 'Prueba no encontrada.');
            return $this->redirect('/admin/atletas');
        }

        $db = Database::connection();
        $originalFecha = $db->query("SELECT a.fecha FROM resultados_pruebas rp INNER JOIN actividades a ON rp.actividad_id = a.actividad_id WHERE rp.test_id = $id")->fetchColumn();
        $fechaEvaluacion = (string) $request->input('fecha_evaluacion', $originalFecha ?: date('Y-m-d'));
        
        $entrenadorId = (int) $request->input('entrenador_id');
        if (!$entrenadorId) {
            $entrenadorId = (int) $db->query("SELECT a.usuario_id FROM resultados_pruebas rp INNER JOIN actividades a ON rp.actividad_id = a.actividad_id WHERE rp.test_id = $id")->fetchColumn();
        }
        if (!$entrenadorId) {
            $entrenadorId = (int) $db->query("SELECT usuario_id FROM usuarios WHERE rol_id = " . ROL_ENTRENADOR . " LIMIT 1")->fetchColumn();
        }

        $eventoId = 0;

        // Buscar si ya existe una actividad de Pruebas Físicas en esa fecha para ese entrenador
        $eventoId = (int) $db->query("SELECT actividad_id FROM actividades WHERE fecha = '$fechaEvaluacion' AND tipo_actividad = 2 AND usuario_id = $entrenadorId LIMIT 1")->fetchColumn();
        
        if (!$eventoId) {
            if ($entrenadorId) {
                $stmt = $db->prepare("INSERT INTO actividades (usuario_id, tipo_actividad, fecha, ubicacion) VALUES (?, 2, ?, ?)");
                $stmt->execute([$entrenadorId, $fechaEvaluacion, 'Cancha Principal']);
                $eventoId = (int) $db->lastInsertId();
            }
        }

        if (!$eventoId) {
            $msg = 'No se pudo determinar un evento o entrenador para registrar la prueba.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $msg], 400);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/{$prueba['atleta_id']}?tab=tab-pruebas");
        }

        // Validar que no exista otra prueba física registrada para este atleta en la fecha seleccionada (excluyendo la actual)
        $exists = (int) $db->query("SELECT COUNT(*) FROM resultados_pruebas rp 
                                    INNER JOIN actividades a ON rp.actividad_id = a.actividad_id 
                                    WHERE rp.atleta_id = {$prueba['atleta_id']} AND DATE(a.fecha) = '$fechaEvaluacion' AND rp.test_id != $id")->fetchColumn();
        if ($exists > 0) {
            $msg = 'Ya existe otra prueba física registrada para este atleta en la fecha seleccionada (' . date('d/m/Y', strtotime($fechaEvaluacion)) . '). Por favor, edita el registro existente.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $msg], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/{$prueba['atleta_id']}?tab=tab-pruebas");
        }

        $data = [
            'actividad_id'      => $eventoId,
            'test_de_fuerza'    => $this->num($request->input('test_de_fuerza')),
            'test_resistencia'  => $this->num($request->input('test_resistencia')),
            'test_velocidad'    => $this->num($request->input('test_velocidad')),
            'test_coordinacion' => $this->num($request->input('test_coordinacion')),
            'test_de_reaccion'  => $this->num($request->input('test_de_reaccion')),
        ];

        $v = \App\Core\Validator::make($data, [
            'test_de_fuerza'    => 'numeric|min:1|max:100',
            'test_resistencia'  => 'numeric|min:1|max:10000',
            'test_velocidad'    => 'numeric|min:1|max:10',
            'test_coordinacion' => 'numeric|min:1|max:100',
            'test_de_reaccion'  => 'numeric|min:10|max:2000',
        ], [
            'test_de_fuerza'    => 'El salto CMJ (Fuerza) debe ser un número entre 1 y 100 cm.',
            'test_resistencia'  => 'El Yo-Yo Test (Resistencia) debe ser un número entre 1 y 10000 metros.',
            'test_velocidad'    => 'El Sprint 30m (Velocidad) debe ser un número entre 1.00 y 10.00 segundos.',
            'test_coordinacion' => 'El Circuito de Conos (Coordinación) debe ser un número entre 1 y 100 segundos.',
            'test_de_reaccion'  => 'La App Cognitiva (Reacción) debe ser un número entre 10 y 2000 ms.',
        ]);

        if (!$v->validate()) {
            $firstError = array_values($v->errors())[0];
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $firstError, 'errors' => $v->errors()], 422);
            }
            flash('error', $firstError);
            return $this->redirect("/admin/atletas/{$prueba['atleta_id']}?tab=tab-pruebas");
        }

        // Validar que no todos los campos estén nulos
        if ($data['test_de_fuerza'] === null && 
            $data['test_resistencia'] === null && 
            $data['test_velocidad'] === null && 
            $data['test_coordinacion'] === null && 
            $data['test_de_reaccion'] === null) {
            
            $msg = 'Debes ingresar al menos el resultado de una prueba física para guardar los cambios.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $msg], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/{$prueba['atleta_id']}?tab=tab-pruebas");
        }

        try {
            (new ResultadoPrueba())->update($id, $data);

            if ($request->isAjax() || $request->isJson()) {
                flash('success', 'Prueba física actualizada correctamente.');
                return Response::json(['success' => true, 'message' => 'Prueba actualizada correctamente.']);
            }

            flash('success', 'Prueba actualizada.');
            return $this->redirect("/admin/atletas/{$prueba['atleta_id']}?tab=tab-pruebas");
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()], 500);
            }
            flash('error', 'No se pudo actualizar: ' . $e->getMessage());
            return $this->redirect("/admin/atletas/{$prueba['atleta_id']}?tab=tab-pruebas");
        }
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atletaId = (int) $request->query('atleta_id');
        $redirectUrl = $request->query('redirect', "/admin/atletas/{$atletaId}?tab=tab-pruebas");

        try {
            $deleted = (new ResultadoPrueba())->delete($id);
            if ($deleted) {
                if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                    return Response::json(['success' => true, 'message' => 'Prueba física eliminada correctamente.']);
                }
                flash('success', 'Prueba física eliminada correctamente.');
            } else {
                if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                    return Response::json(['success' => false, 'message' => 'La prueba no existe o ya fue eliminada.'], 404);
                }
                flash('error', 'La prueba no existe o ya fue eliminada.');
            }
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => false, 'message' => 'No se pudo eliminar la prueba: ' . $e->getMessage()], 500);
            }
            flash('error', 'No se pudo eliminar la prueba: ' . $e->getMessage());
        }

        return $this->redirect($redirectUrl);
    }

    public function crear(Request $request): Response
    {
        $categorias = (new Categoria())->activas();
        return $this->view('resultados_pruebas.crear', [
            'title' => 'Registrar Pruebas Físicas',
            'active' => 'resultados_pruebas',
            'breadcrumb' => ['Inicio', 'Pruebas físicas', 'Registrar'],
            'categorias' => $categorias,
        ], 'admin');
    }

    public function guardar(Request $request): Response
    {
        $fechaEvento = (string) $request->input('fecha_evento', date('Y-m-d'));
        $categoriaId = (int) $request->input('categoria_id', 0);
        $entrenadorId = (int) Auth::id();

        $db = Database::connection();
        
        // Obtener asignacion_id de la categoría
        $stmtAsig = $db->prepare("SELECT asignacion_id FROM asig_categorias WHERE categoria_id = ? AND estatus = 1 LIMIT 1");
        $stmtAsig->execute([$categoriaId]);
        $asignacionId = $stmtAsig->fetchColumn();
        $asignacionId = $asignacionId ? (int)$asignacionId : null;
        if (!$asignacionId) {
            $stmtAsig = $db->prepare("SELECT asignacion_id FROM asig_categorias WHERE categoria_id = ? LIMIT 1");
            $stmtAsig->execute([$categoriaId]);
            $asignacionId = $stmtAsig->fetchColumn();
            $asignacionId = $asignacionId ? (int)$asignacionId : null;
        }

        $dataActividad = [
            'usuario_id'     => $entrenadorId,
            'tipo_actividad' => 2,
            'asignacion_id'  => $asignacionId,
            'fecha'          => $fechaEvento,
            'hora_inicio'    => $request->input('hora_inicio') ?: null,
            'hora_fin'       => $request->input('hora_fin') ?: null,
            'ubicacion'      => $request->input('ubicacion') ?: 'Cancha Principal',
            'terreno'        => $request->input('terreno') !== '' ? (int)$request->input('terreno') : null,
            'clima'          => $request->input('clima') !== '' ? (int)$request->input('clima') : null,
        ];

        $v = Validator::make([
            'fecha'        => $fechaEvento,
            'categoria_id' => $categoriaId ?: null,
        ], [
            'fecha'        => 'required|date',
            'categoria_id' => 'required|integer',
        ]);

        if (!$v->validate()) {
            $this->withOld($request->body());
            $this->withErrors($v->errors());
            return $this->redirect('/admin/resultados-pruebas/crear');
        }

        // Validaciones de fecha
        $minDate = strtotime('2019-01-01');
        $eventDate = strtotime($fechaEvento);
        if ($eventDate > strtotime(date('Y-m-d'))) {
            $this->withOld($request->body());
            flash('error', 'La fecha de evaluación no puede ser en el futuro.');
            return $this->redirect('/admin/resultados-pruebas/crear');
        }
        if ($eventDate < $minDate) {
            $this->withOld($request->body());
            flash('error', 'No se pueden registrar pruebas anteriores al año 2019.');
            return $this->redirect('/admin/resultados-pruebas/crear');
        }
        if (!empty($dataActividad['hora_inicio']) && !empty($dataActividad['hora_fin']) && strtotime($dataActividad['hora_inicio']) >= strtotime($dataActividad['hora_fin'])) {
            $this->withOld($request->body());
            flash('error', 'La hora de inicio debe ser menor a la hora de fin.');
            return $this->redirect('/admin/resultados-pruebas/crear');
        }

        // Validar duplicidad
        $stmt = $db->prepare("
            SELECT a.actividad_id 
            FROM actividades a
            LEFT JOIN asig_categorias ac ON a.asignacion_id = ac.asignacion_id
            LEFT JOIN resultados_pruebas rp ON a.actividad_id = rp.actividad_id
            LEFT JOIN asig_categorias ac2 ON rp.atleta_id = ac2.atleta_id
            WHERE a.fecha = ? AND a.tipo_actividad = 2 AND (ac.categoria_id = ? OR ac2.categoria_id = ?)
            LIMIT 1
        ");
        $stmt->execute([$fechaEvento, $categoriaId, $categoriaId]);
        if ($stmt->fetch()) {
            $this->withOld($request->body());
            flash('error', 'Ya existe un registro de Pruebas Físicas para esta categoría en la fecha seleccionada.');
            return $this->redirect('/admin/resultados-pruebas/crear');
        }

        $atletas = (array) ($request->body('selected_atletas') ?? []);
        if (empty($atletas)) {
            $this->withOld($request->body());
            flash('error', 'Debes evaluar al menos a un atleta.');
            return $this->redirect('/admin/resultados-pruebas/crear');
        }

        $fuerzaArr = (array) ($request->body('test_de_fuerza') ?? []);
        $resistenciaArr = (array) ($request->body('test_resistencia') ?? []);
        $velocidadArr = (array) ($request->body('test_velocidad') ?? []);
        $coordinacionArr = (array) ($request->body('test_coordinacion') ?? []);
        $reaccionArr = (array) ($request->body('test_de_reaccion') ?? []);

        $resultados = [];
        $errors = [];

        foreach ($atletas as $atletaId) {
            $atletaId = (int)$atletaId;
            if (!$atletaId) continue;

            $atletaObj = (new Atleta())->findCompleto($atletaId);
            if (!$atletaObj || in_array((int)$atletaObj['estatus'], [0, 3], true)) {
                $errors[] = "No es posible registrar pruebas físicas para atletas inactivos o suspendidos.";
                break;
            }

            $fuerza = $this->num($fuerzaArr[$atletaId] ?? '');
            $resistencia = $this->num($resistenciaArr[$atletaId] ?? '');
            $velocidad = $this->num($velocidadArr[$atletaId] ?? '');
            $coordinacion = $this->num($coordinacionArr[$atletaId] ?? '');
            $reaccion = $this->num($reaccionArr[$atletaId] ?? '');

            if ($fuerza === null && $resistencia === null && $velocidad === null && $coordinacion === null && $reaccion === null) {
                $errors[] = "Debes ingresar al menos el resultado de una prueba física para el atleta " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . ".";
                continue;
            }

            // Validar rangos
            if ($fuerza !== null && ($fuerza < 1 || $fuerza > 100)) {
                $errors[] = "El salto CMJ (Fuerza) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1 y 100 cm.";
            }
            if ($resistencia !== null && ($resistencia < 1 || $resistencia > 10000)) {
                $errors[] = "El Yo-Yo Test (Resistencia) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1 y 10000 metros.";
            }
            if ($velocidad !== null && ($velocidad < 1.00 || $velocidad > 10.00)) {
                $errors[] = "El Sprint 30m (Velocidad) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1.00 y 10.00 segundos.";
            }
            if ($coordinacion !== null && ($coordinacion < 1 || $coordinacion > 200)) {
                $errors[] = "El Circuito de Conos (Coordinación) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1 y 200 segundos.";
            }
            if ($reaccion !== null && ($reaccion < 10 || $reaccion > 2000)) {
                $errors[] = "La App Cognitiva (Reacción) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 10 y 2000 ms.";
            }

            $resultados[] = [
                'atleta_id' => $atletaId,
                'test_de_fuerza' => $fuerza,
                'test_resistencia' => $resistencia,
                'test_velocidad' => $velocidad,
                'test_coordinacion' => $coordinacion,
                'test_de_reaccion' => $reaccion,
            ];
        }

        if (!empty($errors)) {
            $this->withOld($request->body());
            flash('error', $errors[0]);
            return $this->redirect('/admin/resultados-pruebas/crear');
        }

        try {
            (new ResultadoPrueba())->registrarLote($dataActividad, $resultados);
            flash('success', 'Pruebas físicas registradas correctamente.');
            return $this->redirect('/admin/resultados-pruebas');
        } catch (\Throwable $e) {
            $this->withOld($request->body());
            flash('error', 'Error al guardar los resultados: ' . $e->getMessage());
            return $this->redirect('/admin/resultados-pruebas/crear');
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
             WHERE a.actividad_id = ?"
        );
        $actividad->execute([$id]);
        $actividad = $actividad->fetch();

        if (!$actividad) {
            flash('error', 'Sesión no encontrada.');
            return $this->redirect('/admin/resultados-pruebas');
        }

        $detallesStmt = $db->prepare(
            "SELECT rp.*, atl.nombre, atl.apellido, atl.cedula, atl.foto
             FROM resultados_pruebas rp
             JOIN atletas atl ON rp.atleta_id = atl.atleta_id
             WHERE rp.actividad_id = ?
             ORDER BY atl.apellido, atl.nombre"
        );
        $detallesStmt->execute([$id]);
        $detalles = $detallesStmt->fetchAll();

        return $this->view('resultados_pruebas.show', [
            'title' => 'Detalle de Pruebas Físicas',
            'active' => 'resultados_pruebas',
            'breadcrumb' => ['Inicio', 'Pruebas físicas', 'Detalle'],
            'actividad' => $actividad,
            'detalles' => $detalles
        ], 'admin');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        $actividad = $db->prepare(
            "SELECT a.*,
             (SELECT ac.categoria_id FROM asig_categorias ac WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS categoria_id,
             (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria
             FROM actividades a
             WHERE a.actividad_id = ?"
        );
        $actividad->execute([$id]);
        $actividad = $actividad->fetch();

        if (!$actividad) {
            flash('error', 'Registro de sesión no encontrado.');
            return $this->redirect('/admin/resultados-pruebas');
        }

        // Obtener atletas de la categoría con sus posibles resultados en esta sesión
        $atletasStmt = $db->prepare(
            "SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.foto, a.estatus AS atleta_estatus,
                    rp.test_id, rp.test_de_fuerza, rp.test_resistencia, rp.test_velocidad, rp.test_coordinacion, rp.test_de_reaccion
             FROM asig_categorias ac
             JOIN atletas a ON a.atleta_id = ac.atleta_id
             LEFT JOIN resultados_pruebas rp ON rp.atleta_id = a.atleta_id AND rp.actividad_id = :act_id
             WHERE ac.categoria_id = :cat_id AND ac.estatus = 1
             ORDER BY a.apellido, a.nombre"
        );
        $atletasStmt->execute([
            ':act_id' => $id,
            ':cat_id' => (int)$actividad['categoria_id']
        ]);
        $detalles = $atletasStmt->fetchAll();

        return $this->view('resultados_pruebas.editar', [
            'title' => 'Editar Pruebas Físicas',
            'active' => 'resultados_pruebas',
            'breadcrumb' => ['Inicio', 'Pruebas físicas', 'Editar'],
            'actividad' => $actividad,
            'detalles' => $detalles,
        ], 'admin');
    }

    public function actualizar(Request $request): Response
    {
        $id = (int) $request->param('id');
        $db = Database::connection();

        // Cargar actividad
        $actividad = $db->query("SELECT * FROM actividades WHERE actividad_id = $id AND tipo_actividad = 2")->fetch();
        if (!$actividad) {
            flash('error', 'Sesión no encontrada.');
            return $this->redirect('/admin/resultados-pruebas');
        }

        $fechaEvento = (string) $request->input('fecha_evento', date('Y-m-d'));
        $entrenadorId = (int) Auth::id();

        $dataActividad = [
            'usuario_id'     => $entrenadorId,
            'tipo_actividad' => 2,
            'fecha'          => $fechaEvento,
            'hora_inicio'    => $request->input('hora_inicio') ?: null,
            'hora_fin'       => $request->input('hora_fin') ?: null,
            'ubicacion'      => $request->input('ubicacion') ?: 'Cancha Principal',
            'terreno'        => $request->input('terreno') !== '' ? (int)$request->input('terreno') : null,
            'clima'          => $request->input('clima') !== '' ? (int)$request->input('clima') : null,
        ];

        // Validaciones básicas de fecha
        $minDate = strtotime('2019-01-01');
        $eventDate = strtotime($fechaEvento);
        if ($eventDate > strtotime(date('Y-m-d'))) {
            flash('error', 'La fecha de evaluación no puede ser en el futuro.');
            return $this->redirect("/admin/resultados-pruebas/sesion/{$id}/editar");
        }
        if ($eventDate < $minDate) {
            flash('error', 'No se pueden registrar pruebas anteriores al año 2019.');
            return $this->redirect("/admin/resultados-pruebas/sesion/{$id}/editar");
        }
        if (!empty($dataActividad['hora_inicio']) && !empty($dataActividad['hora_fin']) && strtotime($dataActividad['hora_inicio']) >= strtotime($dataActividad['hora_fin'])) {
            flash('error', 'La hora de inicio debe ser menor a la hora de fin.');
            return $this->redirect("/admin/resultados-pruebas/sesion/{$id}/editar");
        }

        $atletas = (array) ($request->body('selected_atletas') ?? []);
        if (empty($atletas)) {
            flash('error', 'Debes evaluar al menos a un atleta.');
            return $this->redirect("/admin/resultados-pruebas/sesion/{$id}/editar");
        }

        $fuerzaArr = (array) ($request->body('test_de_fuerza') ?? []);
        $resistenciaArr = (array) ($request->body('test_resistencia') ?? []);
        $velocidadArr = (array) ($request->body('test_velocidad') ?? []);
        $coordinacionArr = (array) ($request->body('test_coordinacion') ?? []);
        $reaccionArr = (array) ($request->body('test_de_reaccion') ?? []);

        $resultados = [];
        $errors = [];

        foreach ($atletas as $atletaId) {
            $atletaId = (int)$atletaId;
            if (!$atletaId) continue;

            $atletaObj = (new Atleta())->findCompleto($atletaId);
            if (!$atletaObj || in_array((int)$atletaObj['estatus'], [0, 3], true)) {
                $errors[] = "No es posible registrar pruebas físicas para atletas inactivos o suspendidos.";
                break;
            }

            $fuerza = $this->num($fuerzaArr[$atletaId] ?? '');
            $resistencia = $this->num($resistenciaArr[$atletaId] ?? '');
            $velocidad = $this->num($velocidadArr[$atletaId] ?? '');
            $coordinacion = $this->num($coordinacionArr[$atletaId] ?? '');
            $reaccion = $this->num($reaccionArr[$atletaId] ?? '');

            if ($fuerza === null && $resistencia === null && $velocidad === null && $coordinacion === null && $reaccion === null) {
                $errors[] = "Debes ingresar al menos el resultado de una prueba física para el atleta " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . ".";
                continue;
            }

            // Validar rangos
            if ($fuerza !== null && ($fuerza < 1 || $fuerza > 100)) {
                $errors[] = "El salto CMJ (Fuerza) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1 y 100 cm.";
            }
            if ($resistencia !== null && ($resistencia < 1 || $resistencia > 10000)) {
                $errors[] = "El Yo-Yo Test (Resistencia) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1 y 10000 metros.";
            }
            if ($velocidad !== null && ($velocidad < 1.00 || $velocidad > 10.00)) {
                $errors[] = "El Sprint 30m (Velocidad) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1.00 y 10.00 segundos.";
            }
            if ($coordinacion !== null && ($coordinacion < 1 || $coordinacion > 200)) {
                $errors[] = "El Circuito de Conos (Coordinación) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 1 y 200 segundos.";
            }
            if ($reaccion !== null && ($reaccion < 10 || $reaccion > 2000)) {
                $errors[] = "La App Cognitiva (Reacción) para " . e($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) . " debe estar entre 10 y 2000 ms.";
            }

            $resultados[] = [
                'atleta_id' => $atletaId,
                'test_de_fuerza' => $fuerza,
                'test_resistencia' => $resistencia,
                'test_velocidad' => $velocidad,
                'test_coordinacion' => $coordinacion,
                'test_de_reaccion' => $reaccion,
            ];
        }

        if (!empty($errors)) {
            flash('error', $errors[0]);
            return $this->redirect("/admin/resultados-pruebas/sesion/{$id}/editar");
        }

        try {
            (new ResultadoPrueba())->actualizarLote($id, $dataActividad, $resultados);
            flash('success', 'Pruebas físicas actualizadas correctamente.');
            return $this->redirect('/admin/resultados-pruebas');
        } catch (\Throwable $e) {
            flash('error', 'Error al actualizar los resultados: ' . $e->getMessage());
            return $this->redirect("/admin/resultados-pruebas/sesion/{$id}/editar");
        }
    }

    public function eliminarSesion(Request $request): Response
    {
        $id = (int) $request->param('id');
        try {
            (new ResultadoPrueba())->eliminarLote($id);
            flash('success', 'Sesión de pruebas físicas eliminada correctamente.');
        } catch (\Throwable $e) {
            flash('error', 'No se pudo eliminar la sesión: ' . $e->getMessage());
        }

        return $this->redirect('/admin/resultados-pruebas');
    }

    private function num(mixed $v): ?float
    {
        if ($v === '' || $v === null) return null;
        return is_numeric($v) ? (float) $v : null;
    }
}
