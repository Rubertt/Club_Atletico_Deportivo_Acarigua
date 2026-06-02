<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Atleta;
use App\Models\ResultadoPrueba;

final class ResultadosPruebasController extends Controller
{
    public function index(Request $request): Response
    {
        $pag = (new Atleta())->paginate(['estatus' => 1], (int) $request->query('page', 1), 20);
        return $this->view('resultados_pruebas.index', [
            'title' => 'Pruebas físicas',
            'active' => 'pruebas',
            'breadcrumb' => ['Inicio', 'Reportes', 'Pruebas físicas'],
            'pag' => $pag,
        ], 'admin');
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
                flash('success', 'Prueba física eliminada correctamente.');
            } else {
                flash('error', 'La prueba no existe o ya fue eliminada.');
            }
        } catch (\Throwable $e) {
            flash('error', 'No se pudo eliminar la prueba: ' . $e->getMessage());
        }

        return $this->redirect($redirectUrl);
    }

    private function num(mixed $v): ?float
    {
        if ($v === '' || $v === null) return null;
        return is_numeric($v) ? (float) $v : null;
    }
}
