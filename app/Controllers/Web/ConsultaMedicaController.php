<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Auth;
use App\Models\Atleta;
use App\Models\ConsultaMedica;
use App\Services\ConsultaMedicaService;
use Throwable;

final class ConsultaMedicaController extends Controller
{
    /**
     * Guarda una consulta médica.
     */
    public function store(Request $request): Response
    {
        $atletaId = (int) $request->param('id');
        $atleta = (new Atleta())->find($atletaId);
        if (!$atleta) {
            if ($request->header('Accept') === 'application/json') {
                return $this->json(['success' => false, 'message' => 'Atleta no encontrado.'], 404);
            }
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }

        $creadoEnInput = trim((string) $request->input('creado_en', ''));
        if (empty($creadoEnInput)) {
            $creadoEnInput = date('Y-m-d H:i:s');
        }

        $data = [
            'atleta_id'              => $atletaId,
            'usuario_id'             => Auth::id(),
            'tipo_consulta'          => $request->input('tipo_consulta') !== '' ? (int) $request->input('tipo_consulta') : null,
            'diagnostico'            => trim((string) $request->input('diagnostico', '')),
            'descripcion'            => trim((string) $request->input('descripcion', '')),
            'tratamiento_indicado'   => trim((string) $request->input('tratamiento_indicado', '')),
            'fecha_suceso'           => trim((string) $request->input('fecha_suceso', '')),
            'fecha_alta_estimada'    => trim((string) $request->input('fecha_alta_estimada', '')),
            'estatus_disponibilidad' => $request->input('estatus_disponibilidad') !== '' ? (int) $request->input('estatus_disponibilidad') : null,
            'creado_en'              => $creadoEnInput,
        ];

        // Validaciones básicas con el Validator
        $validator = Validator::make($data, [
            'tipo_consulta'          => 'required|integer|in:1,2,3,4,5,6,7',
            'estatus_disponibilidad' => 'required|integer|in:0,1',
            'diagnostico'            => 'required|max:255',
            'descripcion'            => 'max:255',
            'tratamiento_indicado'   => 'max:255',
            'fecha_suceso'           => 'required|date',
        ]);

        $validator->validate();
        $errors = $validator->errors();

        // Validaciones personalizadas de fecha
        if (!empty($data['fecha_suceso'])) {
            $fechaSuceso = $data['fecha_suceso'];
            $hoy = date('Y-m-d');
            $diezAnosAtras = date('Y-m-d', strtotime('-10 years'));

            if ($fechaSuceso > $hoy) {
                $errors['fecha_suceso'] = 'La fecha de la consulta no puede ser una fecha futura.';
            } elseif ($fechaSuceso < $diezAnosAtras) {
                $errors['fecha_suceso'] = 'La consulta no puede tener una antigüedad mayor a 10 años.';
            } else {
                // Validar límite diario de 3 consultas
                if (!(new ConsultaMedica())->validarLimiteConsultas($atletaId, $fechaSuceso)) {
                    $errors['fecha_suceso'] = 'Límite de registro por fecha de suceso alcanzado';
                }
            }
        }

        if (!empty($data['fecha_alta_estimada'])) {
            $fechaAlta = $data['fecha_alta_estimada'];
            $fechaSuceso = $data['fecha_suceso'];
            $tresAnosFuturo = date('Y-m-d', strtotime('+3 years'));

            if (!empty($fechaSuceso) && $fechaAlta <= $fechaSuceso) {
                $errors['fecha_alta_estimada'] = 'La fecha de recuperación estimada debe ser posterior a la fecha del suceso.';
            } elseif ($fechaAlta > $tresAnosFuturo) {
                $errors['fecha_alta_estimada'] = 'La fecha de recuperacion estimada no puede superar los 3 años a futuro.';
            }
        }

        if (!empty($errors)) {
            if ($request->header('Accept') === 'application/json') {
                return $this->json(['success' => false, 'message' => implode(' ', $errors), 'errors' => $errors], 422);
            }
            $this->withErrors($errors)->withOld($data);
            flash('error', implode(' ', $errors));
            return $this->redirect("/admin/atletas/{$atletaId}?tab=tab-consulta");
        }

        try {
            (new ConsultaMedicaService())->crear($data);
            flash('success', 'Consulta médica registrada correctamente.');
            return $this->json(['success' => true]);
        } catch (Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Error al guardar el registro en la base de datos.'], 500);
        }
    }

    /**
     * Actualiza una consulta médica.
     */
    public function update(Request $request): Response
    {
        $atletaId = (int) $request->param('id');
        $consultaId = (int) $request->param('consulta_id');

        $atleta = (new Atleta())->find($atletaId);
        $consulta = (new ConsultaMedica())->find($consultaId);

        if (!$atleta || !$consulta) {
            return $this->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
        }

        $data = [
            'atleta_id'              => $atletaId,
            'usuario_id'             => Auth::id(),
            'tipo_consulta'          => $request->input('tipo_consulta') !== '' ? (int) $request->input('tipo_consulta') : null,
            'diagnostico'            => trim((string) $request->input('diagnostico', '')),
            'descripcion'            => trim((string) $request->input('descripcion', '')),
            'tratamiento_indicado'   => trim((string) $request->input('tratamiento_indicado', '')),
            'fecha_suceso'           => trim((string) $request->input('fecha_suceso', '')),
            'fecha_alta_estimada'    => trim((string) $request->input('fecha_alta_estimada', '')),
            'estatus_disponibilidad' => $request->input('estatus_disponibilidad') !== '' ? (int) $request->input('estatus_disponibilidad') : null,
        ];

        // Validaciones
        $validator = Validator::make($data, [
            'tipo_consulta'          => 'required|integer|in:1,2,3,4,5,6,7',
            'estatus_disponibilidad' => 'required|integer|in:0,1',
            'diagnostico'            => 'required|max:255',
            'descripcion'            => 'max:255',
            'tratamiento_indicado'   => 'max:255',
            'fecha_suceso'           => 'required|date',
        ]);

        $validator->validate();
        $errors = $validator->errors();

        // Validaciones personalizadas de fecha
        if (!empty($data['fecha_suceso'])) {
            $fechaSuceso = $data['fecha_suceso'];
            $hoy = date('Y-m-d');
            $diezAnosAtras = date('Y-m-d', strtotime('-10 years'));

            if ($fechaSuceso > $hoy) {
                $errors['fecha_suceso'] = 'La fecha de la consulta no puede ser una fecha futura.';
            } elseif ($fechaSuceso < $diezAnosAtras) {
                $errors['fecha_suceso'] = 'La consulta no puede tener una antigüedad mayor a 10 años.';
            } else {
                // Validar límite diario de 3 consultas (excluyendo el registro actual)
                if (!(new ConsultaMedica())->validarLimiteConsultas($atletaId, $fechaSuceso, $consultaId)) {
                    $errors['fecha_suceso'] = 'Límite de registro por fecha de suceso alcanzado';
                }
            }
        }

        if (!empty($data['fecha_alta_estimada'])) {
            $fechaAlta = $data['fecha_alta_estimada'];
            $fechaSuceso = $data['fecha_suceso'];
            $tresAnosFuturo = date('Y-m-d', strtotime('+3 years'));

            if (!empty($fechaSuceso) && $fechaAlta <= $fechaSuceso) {
                $errors['fecha_alta_estimada'] = 'La fecha de recuperación estimada debe ser posterior a la fecha del suceso.';
            } elseif ($fechaAlta > $tresAnosFuturo) {
                $errors['fecha_alta_estimada'] = 'La fecha de recuperacion estimada no puede superar los 3 años a futuro.';
            }
        }

        if (!empty($errors)) {
            return $this->json(['success' => false, 'message' => implode(' ', $errors), 'errors' => $errors], 422);
        }

        try {
            (new ConsultaMedicaService())->actualizar($consultaId, $data);
            flash('success', 'Consulta médica actualizada correctamente.');
            return $this->json(['success' => true]);
        } catch (Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Error al guardar el registro en la base de datos.'], 500);
        }
    }

    /**
     * Elimina una consulta médica.
     */
    public function destroy(Request $request): Response
    {
        $atletaId = (int) $request->param('id');
        $consultaId = (int) $request->param('consulta_id');

        $atleta = (new Atleta())->find($atletaId);
        $consulta = (new ConsultaMedica())->find($consultaId);

        if (!$atleta || !$consulta) {
            flash('error', 'Registro no encontrado.');
            return $this->redirect("/admin/atletas");
        }

        try {
            (new ConsultaMedicaService())->eliminar($consultaId);
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => true, 'message' => 'Consulta médica eliminada correctamente.']);
            }
            flash('success', 'Consulta médica eliminada correctamente.');
        } catch (Throwable $e) {
            if ($request->isAjax() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return Response::json(['success' => false, 'message' => 'Error al eliminar la consulta médica.'], 500);
            }
            flash('error', 'Error al eliminar la consulta médica.');
        }

        return $this->redirect("/admin/atletas/{$atletaId}?tab=tab-consulta");
    }
}
