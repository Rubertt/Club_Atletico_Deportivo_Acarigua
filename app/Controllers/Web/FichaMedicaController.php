<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Atleta;
use App\Models\FichaMedica;

final class FichaMedicaController extends Controller
{
    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) { flash('error', 'No encontrado.'); return $this->redirect('/admin/atletas'); }
        return $this->view('ficha_medica.show', [
            'title' => 'Ficha médica - ' . $atleta['nombre'],
            'active' => 'atletas',
            'breadcrumb' => [
                'Inicio',
                ['label' => 'Atletas', 'url' => url('/admin/atletas')],
                ['label' => $atleta['nombre'] . ' ' . $atleta['apellido'], 'url' => url("/admin/atletas/{$atleta['atleta_id']}")],
                'Ficha médica'
            ],
            'atleta' => $atleta,
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            flash('error', 'No encontrado.');
            return $this->redirect('/admin/atletas');
        }

        $model = new FichaMedica();
        $existente = $model->byAtleta($id);

        if (!$existente && in_array((int)$atleta['estatus'], [0, 3], true)) {
            $msg = 'No es posible registrar una ficha médica para atletas inactivos o suspendidos.';
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => false, 'message' => $msg], 403);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/$id?tab=tab-ficha");
        }

        $payload = [
            'alergias'                 => trim((string) $request->input('alergias', '')),
            'grupo_sanguineo'          => trim((string) $request->input('grupo_sanguineo', '')),
            'antecedentes_familiares'  => trim((string) $request->input('antecedentes_familiares', '')),
            'antecedentes_quirurgicos' => trim((string) $request->input('antecedentes_quirurgicos', '')),
            'condicion_cronica'        => trim((string) $request->input('condicion_cronica', '')),
            'medicacion_actual'        => trim((string) $request->input('medicacion_actual', '')),
        ];

        // Validar que no se guarde el registro completamente vacío (debe ingresar al menos un dato)
        if ($payload['alergias'] === '' &&
            $payload['grupo_sanguineo'] === '' &&
            $payload['antecedentes_familiares'] === '' &&
            $payload['antecedentes_quirurgicos'] === '' &&
            $payload['condicion_cronica'] === '' &&
            $payload['medicacion_actual'] === '') {
            $msg = 'Debe ingresar al menos un dato en la ficha médica (Grupo Sanguíneo, Alergias, Antecedentes, Condición o Medicación).';
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/$id?tab=tab-ficha");
        }

        if ($existente) {
            $model->update((int) $existente['ficha_id'], $payload);
        } else {
            $model->insert(['atleta_id' => $id] + $payload);
        }
        
        if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
            return Response::json(['success' => true, 'message' => 'Ficha médica guardada correctamente.']);
        }
        
        flash('success', 'Ficha médica guardada correctamente.');
        return $this->redirect("/admin/atletas/$id?tab=tab-ficha");
    }

    public function storeDiscapacidad(Request $request): Response
    {
        $atletaId = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($atletaId);
        if (!$atleta) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'Atleta no encontrado.']);
                exit;
            }
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }

        if (in_array((int)$atleta['estatus'], [0, 3], true)) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'No es posible registrar discapacidades para atletas inactivos o suspendidos.']);
                exit;
            }
            flash('error', 'No es posible registrar discapacidades para atletas inactivos o suspendidos.');
            return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
        }
        
        $payload = [
            'tipo_discapacidad_id'    => (int) $request->input('tipo_discapacidad_id'),
            'nro_carnet'              => trim((string) $request->input('nro_carnet', '')),
            'porcentaje_discapacidad' => $request->input('porcentaje_discapacidad') !== '' ? (int) $request->input('porcentaje_discapacidad') : null,
            'fecha_registro'          => date('Y-m-d')
        ];

        if (empty($payload['nro_carnet']) || empty($payload['porcentaje_discapacidad'])) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'El número de carnet y el porcentaje de discapacidad son obligatorios.']);
                exit;
            }
            flash('error', 'El número de carnet y el porcentaje de discapacidad son obligatorios.');
            return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
        }

        if (strlen($payload['nro_carnet']) > 20) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'El número de carnet no puede exceder los 20 caracteres.']);
                exit;
            }
            flash('error', 'El número de carnet no puede exceder los 20 caracteres.');
            return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
        }

        try {
            $db = \App\Core\Database::connection();

            // Verificar carnet duplicado
            $stmtCheck = $db->prepare("SELECT COUNT(*) FROM discapacidades WHERE nro_carnet = :carnet");
            $stmtCheck->execute([':carnet' => $payload['nro_carnet']]);
            if ($stmtCheck->fetchColumn() > 0) {
                if ($request->header('Accept') === 'application/json') {
                    echo json_encode(['success' => false, 'message' => 'Este número de carnet ya está registrado.']);
                    exit;
                }
                flash('error', 'Este número de carnet ya está registrado.');
                return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
            }
            
            // Asegurar que exista una ficha médica primero
            $modelFicha = new FichaMedica();
            $ficha = $modelFicha->byAtleta($atletaId);
            
            if (!$ficha) {
                // Crear ficha en blanco si no existe
                $fichaId = $modelFicha->insert(['atleta_id' => $atletaId]);
            } else {
                $fichaId = $ficha['ficha_id'];
            }

            // Insertar discapacidad
            $stmt = $db->prepare("
                INSERT INTO discapacidades (ficha_id, tipo_discapacidad_id, nro_carnet, porcentaje_discapacidad, fecha_registro) 
                VALUES (:ficha_id, :tipo_id, :carnet, :porcentaje, :fecha)
            ");
            
            $stmt->execute([
                ':ficha_id'   => $fichaId,
                ':tipo_id'    => $payload['tipo_discapacidad_id'],
                ':carnet'     => $payload['nro_carnet'],
                ':porcentaje' => $payload['porcentaje_discapacidad'],
                ':fecha'      => $payload['fecha_registro']
            ]);
            
            if ($request->header('Accept') === 'application/json') {
                flash('success', 'Discapacidad agregada correctamente.');
                echo json_encode(['success' => true]);
                exit;
            }
            flash('success', 'Discapacidad agregada correctamente.');
        } catch (\Throwable $e) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error al guardar la discapacidad.']);
                exit;
            }
            flash('error', 'Ocurrió un error al guardar la discapacidad.');
        }

        return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
    }

    public function updateDiscapacidad(Request $request): Response
    {
        $atletaId = (int) $request->param('id');
        $discId   = (int) $request->param('disc_id');

        $payload = [
            'tipo_discapacidad_id'    => (int) $request->input('tipo_discapacidad_id'),
            'nro_carnet'              => trim((string) $request->input('nro_carnet', '')),
            'porcentaje_discapacidad' => $request->input('porcentaje_discapacidad') !== '' ? (int) $request->input('porcentaje_discapacidad') : null,
        ];

        if (empty($payload['nro_carnet']) || empty($payload['porcentaje_discapacidad'])) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'El número de carnet y el porcentaje de discapacidad son obligatorios.']);
                exit;
            }
            flash('error', 'El número de carnet y el porcentaje de discapacidad son obligatorios.');
            return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
        }

        if (strlen($payload['nro_carnet']) > 20) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'El número de carnet no puede exceder los 20 caracteres.']);
                exit;
            }
            flash('error', 'El número de carnet no puede exceder los 20 caracteres.');
            return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
        }

        try {
            $db = \App\Core\Database::connection();

            // Verificar carnet duplicado
            $stmtCheck = $db->prepare("SELECT COUNT(*) FROM discapacidades WHERE nro_carnet = :carnet AND discapacidad_id != :id");
            $stmtCheck->execute([':carnet' => $payload['nro_carnet'], ':id' => $discId]);
            if ($stmtCheck->fetchColumn() > 0) {
                if ($request->header('Accept') === 'application/json') {
                    echo json_encode(['success' => false, 'message' => 'Este número de carnet ya está registrado.']);
                    exit;
                }
                flash('error', 'Este número de carnet ya está registrado.');
                return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
            }
            
            $stmt = $db->prepare("
                UPDATE discapacidades 
                SET tipo_discapacidad_id = :tipo_id, 
                    nro_carnet = :carnet, 
                    porcentaje_discapacidad = :porcentaje
                WHERE discapacidad_id = :disc_id
            ");
            
            $stmt->execute([
                ':tipo_id'    => $payload['tipo_discapacidad_id'],
                ':carnet'     => $payload['nro_carnet'],
                ':porcentaje' => $payload['porcentaje_discapacidad'],
                ':disc_id'    => $discId
            ]);
            
            if ($request->header('Accept') === 'application/json') {
                flash('success', 'Discapacidad actualizada correctamente.');
                echo json_encode(['success' => true]);
                exit;
            }
            flash('success', 'Discapacidad actualizada correctamente.');
        } catch (\Throwable $e) {
            if ($request->header('Accept') === 'application/json') {
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error al actualizar la discapacidad.']);
                exit;
            }
            flash('error', 'Ocurrió un error al actualizar la discapacidad.');
        }

        return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
    }

    public function destroyDiscapacidad(Request $request): Response
    {
        $atletaId = (int) $request->param('id');
        $discId   = (int) $request->param('disc_id');

        try {
            $db = \App\Core\Database::connection();
            $stmt = $db->prepare("DELETE FROM discapacidades WHERE discapacidad_id = :disc_id");
            $stmt->execute([':disc_id' => $discId]);
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => true, 'message' => 'Discapacidad eliminada.']);
            }
            flash('success', 'Discapacidad eliminada.');
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => false, 'message' => 'No se pudo eliminar la discapacidad.'], 500);
            }
            flash('error', 'No se pudo eliminar la discapacidad.');
        }

        return $this->redirect("/admin/atletas/$atletaId?tab=tab-ficha");
    }
}
