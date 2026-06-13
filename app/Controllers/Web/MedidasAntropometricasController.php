<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Atleta;
use App\Models\MedidaAntropometrica;

final class MedidasAntropometricasController extends Controller
{
    public function index(Request $request): Response
    {
        $pag = (new Atleta())->paginate(['estatus' => 1], (int) $request->query('page', 1), 20);
        return $this->view('medidas.index', [
            'title' => 'Antropometría',
            'active' => 'medidas',
            'breadcrumb' => ['Inicio', 'Antropometría'],
            'pag' => $pag,
        ], 'admin');
    }

    public function atleta(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) { 
            flash('error', 'Atleta no encontrado.'); 
            return $this->redirect('/admin/medidas'); 
        }
        $historial = (new MedidaAntropometrica())->historial($id);
        return $this->view('medidas.atleta', [
            'title' => 'Antropometría - ' . $atleta['nombre'] . ' ' . $atleta['apellido'],
            'active' => 'medidas',
            'breadcrumb' => [
                'Inicio',
                ['label' => 'Antropometría', 'url' => url('/admin/medidas')],
                $atleta['nombre'] . ' ' . $atleta['apellido']
            ],
            'atleta' => $atleta,
            'historial' => $historial,
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/medidas');
        }

        if (in_array((int)$atleta['estatus'], [0, 3], true)) {
            $msg = 'No es posible registrar medidas antropométricas para un atleta suspendido o inactivo.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 403);
            }
            flash('error', $msg);
            return $this->redirect("/admin/medidas/atleta/$id");
        }

        $data = [
            'atleta_id'              => $id,
            'fecha_medicion'         => (string) $request->input('fecha_medicion', date('Y-m-d')),
            'peso'                   => $request->input('peso') !== '' ? (float) $request->input('peso') : null,
            'altura'                 => $request->input('altura') !== '' ? (float) $request->input('altura') : null,
            'porcentaje_grasa'       => $request->input('porcentaje_grasa') !== '' ? (float) $request->input('porcentaje_grasa') : null,
            'porcentaje_musculatura' => $request->input('porcentaje_musculatura') !== '' ? (float) $request->input('porcentaje_musculatura') : null,
            'envergadura'            => $request->input('envergadura') !== '' ? (float) $request->input('envergadura') : null,
            'largo_de_pierna'        => $request->input('largo_de_pierna') !== '' ? (float) $request->input('largo_de_pierna') : null,
            'largo_de_torso'         => $request->input('largo_de_torso') !== '' ? (float) $request->input('largo_de_torso') : null,
        ];

        $v = Validator::make($data, [
            'fecha_medicion' => 'required|date',
            'peso'           => 'numeric|min:1',
            'altura'         => 'numeric|min:0.5',
        ]);

        if (!$v->validate()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors'  => $v->errors()
                ], 422);
            }
            $this->withErrors($v->errors());
            return $this->redirect("/admin/medidas/atleta/$id");
        }

        // Validar que la fecha de medición no sea futura
        if (strtotime($data['fecha_medicion']) > strtotime(date('Y-m-d'))) {
            $msg = 'La fecha de medición no puede ser en el futuro.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/medidas/atleta/$id");
        }

        // Validar que no exista otra medición en la misma fecha para este atleta
        $db = \App\Core\Database::connection();
        $fecha = $data['fecha_medicion'];
        $exists = (int) $db->query("SELECT COUNT(*) FROM medidas_antropometricas WHERE atleta_id = $id AND DATE(fecha_medicion) = '$fecha'")->fetchColumn();
        if ($exists > 0) {
            $msg = 'Ya existe una medición registrada para este atleta en la fecha seleccionada. Por favor, edita el registro existente.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/medidas/atleta/$id");
        }

        // Validar que no se guarde el registro completamente vacío (debe ingresar al menos una medición)
        if ($data['peso'] === null &&
            $data['altura'] === null &&
            $data['porcentaje_grasa'] === null &&
            $data['porcentaje_musculatura'] === null &&
            $data['envergadura'] === null &&
            $data['largo_de_pierna'] === null &&
            $data['largo_de_torso'] === null) {
            $msg = 'Debe ingresar al menos una medición (Peso, Altura, % Grasa, % Musculatura, Envergadura, Pierna o Torso).';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/medidas/atleta/$id");
        }

        try {
            (new MedidaAntropometrica())->insert($data);
            
            if ($request->isAjax() || $request->isJson()) {
                flash('success', 'Medición registrada correctamente.');
                return Response::json([
                    'success' => true,
                    'message' => 'Medición registrada correctamente.'
                ]);
            }
            
            flash('success', 'Medición registrada.');
            return $this->redirect("/admin/medidas/atleta/$id");
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'No se pudo guardar la medición: ' . $e->getMessage()
                ], 500);
            }
            flash('error', 'Error al guardar.');
            return $this->redirect("/admin/medidas/atleta/$id");
        }
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $medidaModel = new MedidaAntropometrica();
        $medida = $medidaModel->find($id);
        if (!$medida) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Medición no encontrada.'], 404);
            }
            flash('error', 'Medición no encontrada.');
            return $this->redirect('/admin/atletas');
        }

        $data = [
            'fecha_medicion'         => (string) $request->input('fecha_medicion', $medida['fecha_medicion']),
            'peso'                   => $request->input('peso') !== '' ? (float) $request->input('peso') : null,
            'altura'                 => $request->input('altura') !== '' ? (float) $request->input('altura') : null,
            'porcentaje_grasa'       => $request->input('porcentaje_grasa') !== '' ? (float) $request->input('porcentaje_grasa') : null,
            'porcentaje_musculatura' => $request->input('porcentaje_musculatura') !== '' ? (float) $request->input('porcentaje_musculatura') : null,
            'envergadura'            => $request->input('envergadura') !== '' ? (float) $request->input('envergadura') : null,
            'largo_de_pierna'        => $request->input('largo_de_pierna') !== '' ? (float) $request->input('largo_de_pierna') : null,
            'largo_de_torso'         => $request->input('largo_de_torso') !== '' ? (float) $request->input('largo_de_torso') : null,
        ];

        $v = Validator::make($data, [
            'fecha_medicion' => 'required|date',
            'peso'           => 'numeric|min:1',
            'altura'         => 'numeric|min:0.5',
        ]);

        if (!$v->validate()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors'  => $v->errors()
                ], 422);
            }
            $this->withErrors($v->errors());
            return $this->redirect("/admin/atletas/" . $medida['atleta_id'] . "?tab=tab-antropometria");
        }

        // Validar que la fecha de medición no sea futura
        if (strtotime($data['fecha_medicion']) > strtotime(date('Y-m-d'))) {
            $msg = 'La fecha de medición no puede ser en el futuro.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/" . $medida['atleta_id'] . "?tab=tab-antropometria");
        }

        // Validar que no exista otra medición en la misma fecha para este atleta (excluyendo la actual)
        $db = \App\Core\Database::connection();
        $fecha = $data['fecha_medicion'];
        $exists = (int) $db->query("SELECT COUNT(*) FROM medidas_antropometricas WHERE atleta_id = {$medida['atleta_id']} AND DATE(fecha_medicion) = '$fecha' AND medidas_id != $id")->fetchColumn();
        if ($exists > 0) {
            $msg = 'Ya existe otra medición registrada para este atleta en la fecha seleccionada. Por favor, edita el registro existente.';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/" . $medida['atleta_id'] . "?tab=tab-antropometria");
        }

        // Validar que no se guarde el registro completamente vacío (debe ingresar al menos una medición)
        if ($data['peso'] === null &&
            $data['altura'] === null &&
            $data['porcentaje_grasa'] === null &&
            $data['porcentaje_musculatura'] === null &&
            $data['envergadura'] === null &&
            $data['largo_de_pierna'] === null &&
            $data['largo_de_torso'] === null) {
            $msg = 'Debe ingresar al menos una medición (Peso, Altura, % Grasa, % Musculatura, Envergadura, Pierna o Torso).';
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => $msg
                ], 422);
            }
            flash('error', $msg);
            return $this->redirect("/admin/atletas/" . $medida['atleta_id'] . "?tab=tab-antropometria");
        }

        try {
            $medidaModel->update($id, $data);
            
            if ($request->isAjax() || $request->isJson()) {
                flash('success', 'Medición actualizada correctamente.');
                return Response::json([
                    'success' => true,
                    'message' => 'Medición actualizada correctamente.'
                ]);
            }
            
            flash('success', 'Medición actualizada.');
            return $this->redirect("/admin/atletas/" . $medida['atleta_id'] . "?tab=tab-antropometria");
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'No se pudo actualizar la medición: ' . $e->getMessage()
                ], 500);
            }
            flash('error', 'Error al actualizar la medición.');
            return $this->redirect("/admin/atletas/" . $medida['atleta_id'] . "?tab=tab-antropometria");
        }
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atletaId = (int) $request->query('atleta_id');
        $redirect = $request->query('redirect', "/admin/medidas/atleta/$atletaId");
        
        try {
            (new MedidaAntropometrica())->delete($id);
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => true, 'message' => 'Medición eliminada correctamente.']);
            }
            flash('success', 'Medición eliminada correctamente.');
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => false, 'message' => 'No se pudo eliminar la medición.'], 500);
            }
            flash('error', 'No se pudo eliminar la medición.');
        }

        return $this->redirect($redirect);
    }
}
