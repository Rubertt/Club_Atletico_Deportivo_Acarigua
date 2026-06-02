<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Logger;
use App\Models\Atleta;
use App\Models\Categoria;
use App\Models\PosicionJuego;
use App\Models\Direccion;
use App\Models\MedidaAntropometrica;
use App\Models\ResultadoPrueba;
use App\Models\Asistencia;
use App\Services\AtletaService;
use Throwable;

final class AtletasController extends Controller
{
    // CAMBIAR ESTE VALOR SI LA COMUNIDAD DECIDE OTRA EDAD MÍNIMA OFICIAL
    public const EDAD_MINIMA_ATLETA = 6;
    public const EDAD_MAXIMA_ATLETA = 70;

    public function index(Request $request): Response
    {
        $filters = [
            'estatus' => $request->query('estatus'),
            'q' => $request->query('q'),
            'categoria_id' => $request->query('categoria_id'),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $atletaModel = new Atleta();
        $data = $atletaModel->paginate(array_filter($filters, fn($v) => $v !== null && $v !== ''), $page, 15);

        // Calcular conteos reales para las tarjetas
        $countsRaw = $atletaModel->countByEstatus();
        $stats = ['activo' => 0, 'lesionado' => 0, 'suspendido' => 0, 'inactivo' => 0];
        foreach ($countsRaw as $c) {
            if ((int) $c['estatus'] === 1)
                $stats['activo'] = (int) $c['total'];
            if ((int) $c['estatus'] === 2)
                $stats['lesionado'] = (int) $c['total'];
            if ((int) $c['estatus'] === 0)
                $stats['suspendido'] = (int) $c['total'];
            if ((int) $c['estatus'] === 3)
                $stats['inactivo'] = (int) $c['total'];
        }

        return $this->view('atletas.index', [
            'title' => 'Atletas',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas'],
            'pag' => $data,
            'filters' => $filters,
            'stats' => $stats,
            'categorias' => (new Categoria())->all('nombre_categoria'),
        ], 'admin');
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }
        $pdo = \App\Core\Database::connection();
        $tipos_discapacidades = $pdo->query("SELECT * FROM tipos_discapacidades ORDER BY nombre_tipo ASC")->fetchAll(\PDO::FETCH_ASSOC);

        $medidasModel = new MedidaAntropometrica();
        $medidas_historial = $medidasModel->historial($id);

        $pruebasModel = new ResultadoPrueba();
        $pruebas_historial = $pruebasModel->historial($id);

        $asistenciaModel = new Asistencia();
        $asistencias_historial = $asistenciaModel->historialAtleta($id);

        $asignaciones = (new \App\Models\AsigCategoria())->athleteAssignments($id);

        return $this->view('atletas.show', [
            'title' => $atleta['nombre'] . ' ' . $atleta['apellido'],
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', $atleta['nombre'] . ' ' . $atleta['apellido']],
            'atleta' => $atleta,
            'tipos_discapacidades' => $tipos_discapacidades,
            'medidas_historial' => $medidas_historial,
            'pruebas_historial' => $pruebas_historial,
            'asistencias_historial' => $asistencias_historial,
            'asignaciones' => $asignaciones,
            'paises'     => (new Direccion())->paises(),
            'entrenadores' => (new \App\Models\Usuario())->entrenadores(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('atletas.form', [
            'title' => 'Nuevo atleta',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Nuevo'],
            'atleta' => null,
            'paises' => (new Direccion())->paises(),
            'action' => url('/admin/atletas'),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->rawInput($request);
        $errors = $this->validar($data)->errors();
        if ($errors) {
            $this->withOld($data)->withErrors($errors);
            if (isset($errors['fecha_nacimiento'])) {
                flash('error', $errors['fecha_nacimiento']);
            }
            return $this->redirect('/admin/atletas/crear');
        }

        try {
            $service = new AtletaService();
            $id = $service->crear($data, $_FILES['foto'] ?? []);
            flash('success', 'Atleta registrado correctamente.');
            return $this->redirect("/admin/atletas/$id");
        } catch (Throwable $e) {
            Logger::error($e);
            $this->withOld($data);
            flash('error', 'No se pudo crear el atleta: ' . $e->getMessage());
            return $this->redirect('/admin/atletas/crear');
        }
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }
        return $this->view('atletas.form', [
            'title' => 'Editar atleta',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Editar'],
            'atleta' => $atleta,
            'paises' => (new Direccion())->paises(),
            'action' => url("/admin/atletas/{$atleta['atleta_id']}"),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atletaModel = new Atleta();
        $actual = $atletaModel->findCompleto($id);
        
        if (!$actual) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Atleta no encontrado.'], 404);
            }
            flash('error', 'Atleta no encontrado.');
            return $this->redirect('/admin/atletas');
        }

        // Combinar datos existentes con los nuevos para permitir actualizaciones parciales (Modales)
        $data = $this->mergeData($actual, $request);
        
        $v = $this->validar($data, $id);
        $errors = $v->errors();
        if ($errors) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $errors
                ], 422);
            }
            $this->withOld($data)->withErrors($errors);
            if (isset($errors['fecha_nacimiento'])) {
                flash('error', $errors['fecha_nacimiento']);
            }
            return $this->redirect("/admin/atletas/$id/editar");
        }

        try {
            (new AtletaService())->actualizar($id, $data, $_FILES['foto'] ?? []);
            
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => true, 'message' => 'Atleta actualizado correctamente.']);
            }
            
            flash('success', 'Atleta actualizado.');
            return $this->redirect("/admin/atletas/$id");
        } catch (Throwable $e) {
            Logger::error($e);
            $msg = 'No se pudo actualizar: ' . $e->getMessage();
            if ($request->isAjax() || $request->isJson()) return Response::json(['success' => false, 'message' => $msg], 500);
            $this->withOld($data);
            flash('error', $msg);
            return $this->redirect("/admin/atletas/$id/editar");
        }
    }

    /**
     * Mezcla los datos actuales del atleta con los recibidos en el request.
     * Esto permite que los modales solo envíen los campos que están editando.
     */
    private function cleanCedulaDots(?string $cedula): ?string
    {
        if (empty($cedula)) return $cedula;
        
        // Si contiene guión
        if (str_contains($cedula, '-')) {
            [$prefix, $num] = explode('-', $cedula, 2);
            $prefixUpper = strtoupper($prefix);
            if ($prefixUpper === 'V' || $prefixUpper === 'E' || $prefixUpper === 'P') {
                return $prefixUpper . '-' . str_replace('.', '', $num);
            }
            return $prefixUpper . '-' . $num;
        }
        
        // Si no contiene guión, pero empieza con una letra de prefijo (ej: V12345678 o V12.345.678)
        $firstChar = strtoupper($cedula[0]);
        if (in_array($firstChar, ['V', 'E', 'P', 'N'])) {
            $num = substr($cedula, 1);
            if ($firstChar === 'V' || $firstChar === 'E' || $firstChar === 'P') {
                return $firstChar . '-' . str_replace('.', '', $num);
            }
            return $firstChar . '-' . $num;
        }
        
        return str_replace('.', '', $cedula);
    }

    private function mergeData(array $actual, Request $request): array
    {
        $input = [
            'nombre'            => $request->input('nombre', $actual['nombre']),
            'apellido'          => $request->input('apellido', $actual['apellido']),
            'cedula'            => $request->input('cedula') !== null ? ($request->input('cedula') ? $this->cleanCedulaDots($request->input('cedula')) : null) : $actual['cedula'],
            'sexo'              => $request->input('sexo', $actual['sexo']),
            'telefono'          => $request->input('telefono') !== null ? ($request->input('telefono') ?: null) : $actual['telefono'],
            'fecha_nacimiento'  => $request->input('fecha_nacimiento', $actual['fecha_nac']),
            'pierna_dominante'  => $request->input('pierna_dominante') !== null ? ($request->input('pierna_dominante') ?: null) : $actual['pierna_dominante'],
            'estatus'           => $request->input('estatus') !== null ? (int) $request->input('estatus') : $actual['estatus'],
            
            'estado_id'         => $request->input('estado_id', $actual['estado_id'] ?? null),
            'municipio_id'      => $request->input('municipio_id', $actual['municipio_id'] ?? null),
            'parroquia_id'      => $request->input('parroquia_id', $actual['parroquias_id']),
            'localidad'         => $request->input('localidad', $actual['localidad']),
            'tipo_vivienda'     => $request->input('tipo_vivienda', $actual['tipo_vivienda']),
            'ubicacion_vivienda'=> $request->input('ubicacion_vivienda', $actual['ubicacion_vivienda']),
            
            'tutor_nombres'     => $request->input('tutor_nombres', $actual['tutor_nombres']),
            'tutor_apellidos'   => $request->input('tutor_apellidos', $actual['tutor_apellidos']),
            'tutor_cedula'      => $this->cleanCedulaDots($request->input('tutor_cedula', $actual['tutor_cedula'])),
            'tutor_telefono'    => $request->input('tutor_telefono', $actual['tutor_telefono']),
            'tutor_relacion'    => $request->input('tutor_relacion', $actual['tutor_relacion']),
            
            'alergias'                 => $request->input('alergias', $actual['alergias']),
            'grupo_sanguineo'          => $request->input('grupo_sanguineo', $actual['grupo_sanguineo']),
            'antecedentes_familiares'  => $request->input('antecedentes_familiares', $actual['antecedentes_familiares']),
            'antecedentes_quirurgicos' => $request->input('antecedentes_quirurgicos', $actual['antecedentes_quirurgicos']),
            'condicion_cronica'        => $request->input('condicion_cronica', $actual['condicion_cronica']),
            'medicacion_actual'        => $request->input('medicacion_actual', $actual['medicacion_actual']),
            'eliminar_foto'            => $request->input('eliminar_foto') === '1',
        ];

        return $input;
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        try {
            // Eliminar ficha médica asociada primero (si existe) para evitar error de llave foránea
            (new \App\Models\FichaMedica())->query('DELETE FROM fichas_medicas WHERE atleta_id = :id', [':id' => $id]);

            (new Atleta())->delete($id);
            Logger::audit('atleta.eliminar', ['atleta_id' => $id]);
            flash('success', 'Atleta eliminado correctamente.');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'No se pudo eliminar el atleta porque tiene registros importantes asociados (ej. asistencias). Sugerencia: cambie su estatus a Inactivo.');
        }
        return $this->redirect('/admin/atletas');
    }

    private function rawInput(Request $request): array
    {
        return [
            'nombre' => trim((string) $request->input('nombre', '')),
            'apellido' => trim((string) $request->input('apellido', '')),
            'cedula' => $this->cleanCedulaDots(trim((string) $request->input('cedula', ''))),
            'sexo' => trim((string) $request->input('sexo', 'M')), // Nuevo campo requerido en BD
            'telefono' => trim((string) $request->input('telefono', '')),
            'fecha_nacimiento' => trim((string) $request->input('fecha_nacimiento', '')),
            'pierna_dominante' => $request->input('pierna_dominante') ?: null,
            'estatus' => $request->input('estatus') !== null ? (int) $request->input('estatus') : 1,

            // Dirección (Adaptado a tabla direcciones)
            'estado_id' => $request->input('estado_id') ?: null,
            'municipio_id' => $request->input('municipio_id') ?: null,
            'parroquia_id' => $request->input('parroquia_id') ?: null,
            'localidad' => trim((string) $request->input('localidad', '')),
            'tipo_vivienda' => trim((string) $request->input('tipo_vivienda', '')),
            'ubicacion_vivienda' => trim((string) $request->input('ubicacion_vivienda', '')),

            // Representante (Adaptado a tabla representante)
            'tutor_nombres' => trim((string) $request->input('tutor_nombres', '')),
            'tutor_apellidos' => trim((string) $request->input('tutor_apellidos', '')),
            'tutor_cedula' => $this->cleanCedulaDots(trim((string) $request->input('tutor_cedula', ''))),
            'tutor_telefono' => trim((string) $request->input('tutor_telefono', '')),
            'tutor_relacion' => trim((string) $request->input('tutor_relacion', 'representante')),

            // Ficha médica (Adaptado a tabla ficha_medica)
            'alergias' => trim((string) $request->input('alergias', '')),
            'grupo_sanguineo' => trim((string) $request->input('grupo_sanguineo', '')),
            'antecedentes_familiares' => trim((string) $request->input('antecedentes_familiares', '')),
            'antecedentes_quirurgicos' => trim((string) $request->input('antecedentes_quirurgicos', '')),
            'condicion_cronica' => trim((string) $request->input('condicion_cronica', '')),
            'medicacion_actual' => trim((string) $request->input('medicacion_actual', '')),
            'eliminar_foto' => $request->input('eliminar_foto') === '1',
        ];
    }

    private function validar(array $data, ?int $ignoreId = null): Validator
    {
        // Regex: cédula venezolana V-NUMERO o E-NUMERO (6 a 10 dígitos) o N-AÑO-ACTA (acta de nacimiento) o P-NUMERO (pasaporte alfanumérico 5 a 15)
        $cedRegex = '/^([VE]-\d{6,10}|N-\d{4}-[A-Z0-9]{1,5}|P-[A-Z0-9]{5,15})$/i';
        // Regex: teléfono 11 dígitos con prefijo venezolano (prefijo 4 dígitos + 7 dígitos = 11 total)
        $telRegex = '/^0(412|414|416|422|424|426|255|256)\d{7}$/';

        $rules = [
            'nombre' => 'required|min:2|max:100',
            'apellido' => 'required|min:2|max:100',
            'fecha_nacimiento' => 'required|date',
            'estatus' => 'required|in:0,1,2,3',
            'pierna_dominante' => 'in:derecha,izquierda,ambidiestro',
        ];

        // Calcular edad en años en el backend para validar de manera dinámica
        $age = 0;
        if (!empty($data['fecha_nacimiento'])) {
            $birthDate = strtotime($data['fecha_nacimiento']);
            if ($birthDate !== false) {
                $age = (int) date('Y') - (int) date('Y', $birthDate);
                if (date('md') < date('md', $birthDate)) {
                    $age--;
                }
            }
        }

        // 1. Cédula/Folio obligatoria si es mayor de 9 años
        $cedulaRules = [];
        if ($age > 9) {
            $cedulaRules[] = 'required';
            $cedulaRules[] = "regex:$cedRegex";
        } elseif (!empty($data['cedula'])) {
            $cedulaRules[] = "regex:$cedRegex";
        }

        if (!empty($data['cedula'])) {
            if ($ignoreId) {
                $cedulaRules[] = "unique:atletas,cedula,atleta_id:$ignoreId";
            } else {
                $cedulaRules[] = 'unique:atletas,cedula';
            }
        }

        if (!empty($cedulaRules)) {
            $rules['cedula'] = $cedulaRules;
        }

        // 2. Teléfono personal obligatorio si es mayor de edad
        if ($age >= 18) {
            $rules['telefono'] = ['required', "regex:$telRegex"];
        } elseif (!empty($data['telefono'])) {
            $rules['telefono'] = ["regex:$telRegex"];
        }

        // 3. Datos del representante obligatorios si es menor de edad OR si se edita desde el modal de representante
        $tieneRepresentanteEnPost = ($ignoreId !== null) && isset($_POST['tutor_nombres']);
        if ($age < 18 || $tieneRepresentanteEnPost) {
            $esEdicionBasico = ($ignoreId !== null) && isset($_POST['nombre']) && !isset($_POST['tutor_nombres']);
            $tutorVacio = empty($data['tutor_nombres']) 
                || $data['tutor_nombres'] === 'Sin Nombre' 
                || empty($data['tutor_cedula']) 
                || $data['tutor_cedula'] === 'S/N' 
                || empty($data['tutor_telefono']);

            if ($esEdicionBasico && $tutorVacio && $age < 18) {
                $rules['tutor_representante'] = 'required';
            } else {
                $rules['tutor_nombres'] = 'required|min:2|max:100';
                $rules['tutor_apellidos'] = 'required|min:2|max:100';
                $rules['tutor_cedula'] = ['required', "regex:$cedRegex"];
                $rules['tutor_telefono'] = ['required', "regex:$telRegex"];
                $rules['tutor_relacion'] = 'required';
            }
        } else {
            // Si es mayor de edad y no se envía el modal de representante, es opcional pero se valida formato si existe
            if (!empty($data['tutor_cedula'])) {
                $rules['tutor_cedula'] = ["regex:$cedRegex"];
            }
            if (!empty($data['tutor_telefono'])) {
                $rules['tutor_telefono'] = ["regex:$telRegex"];
            }
        }

        // 4. Validar dirección detallada si estamos en registro o si se envían datos de dirección en el request
        $esRegistro = ($ignoreId === null);
        $tieneDireccionEnRequest = isset($_POST['parroquia_id']) || isset($_POST['localidad']);
        if ($esRegistro || $tieneDireccionEnRequest) {
            $rules['parroquia_id'] = 'required|integer';
            $rules['localidad'] = 'required|min:2|max:200';
            $rules['tipo_vivienda'] = 'required|in:casa,apto,edificio';
            $rules['ubicacion_vivienda'] = 'required|min:2|max:500';
        }

        $messages = [
            'cedula' => 'La cédula del atleta ya está registrada o tiene un formato inválido (Ej: V-12345678, E-12345678 (6 a 10 dígitos, sin puntos), N-AÑO-ACTA o P-Pasaporte). Es obligatoria para mayores de 9 años y debe ser única.',
            'telefono' => 'El teléfono debe comenzar con 0412, 0414, 0416, 0422, 0424, 0255 o 0256 y tener 11 dígitos. Es obligatorio para mayores de edad.',
            'tutor_representante' => 'Para registrar al atleta como menor de edad, primero debe asignar y guardar los datos de su representante en la sección correspondiente de su perfil.',
            'tutor_nombres' => 'El nombre del representante es obligatorio.',
            'tutor_apellidos' => 'El apellido del representante es obligatorio.',
            'tutor_cedula' => 'La cédula o pasaporte del representante es obligatoria y debe tener un formato válido (Ej: V-12345678, E-12345678 (6 a 10 dígitos, sin puntos) o P-Pasaporte).',
            'tutor_telefono' => 'El teléfono del representante es obligatorio y debe tener 11 dígitos.',
            'tutor_relacion' => 'El tipo de relación con el representante es obligatorio.',
            'parroquia_id' => 'La parroquia es obligatoria.',
            'localidad' => 'La localidad o sector es obligatorio y debe tener al menos 2 caracteres.',
            'tipo_vivienda' => 'El tipo de vivienda es obligatorio.',
            'ubicacion_vivienda' => 'La ubicación específica (dirección exacta) es obligatoria y debe tener al menos 2 caracteres.',
        ];

        $v = Validator::make($data, $rules, $messages);
        $v->validate();

        // Validaciones de edad (entre 6 y 100 años) y fecha futura
        if (!empty($data['fecha_nacimiento'])) {
            $birthDate = strtotime($data['fecha_nacimiento']);
            if ($birthDate !== false) {
                if ($birthDate > time()) {
                    $v->addError('fecha_nacimiento', 'La fecha de nacimiento no puede ser en el futuro.');
                } else {
                    $age = (int) date('Y') - (int) date('Y', $birthDate);
                    if (date('md') < date('md', $birthDate)) {
                        $age--;
                    }
                    if ($age < self::EDAD_MINIMA_ATLETA) {
                        $v->addError('fecha_nacimiento', 'El atleta debe tener al menos ' . self::EDAD_MINIMA_ATLETA . ' años de edad.');
                    } elseif ($age > self::EDAD_MAXIMA_ATLETA) {
                        $v->addError('fecha_nacimiento', 'La edad máxima permitida es de ' . self::EDAD_MAXIMA_ATLETA . ' años.');
                    }
                }
            }
        }

        // Validar año de la partida de nacimiento (N-)
        if (!empty($data['cedula']) && str_starts_with(strtoupper($data['cedula']), 'N-')) {
            $parts = explode('-', $data['cedula']);
            if (count($parts) >= 2) {
                $certYear = (int)$parts[1];
                if (!empty($data['fecha_nacimiento'])) {
                    $birthYear = (int)date('Y', strtotime($data['fecha_nacimiento']));
                    if ($certYear < $birthYear) {
                        $v->addError('cedula', 'El año del acta de nacimiento no puede ser menor al año de nacimiento del atleta.');
                    }
                }
            }
        }

        return $v;
    }

    public function validarPaso(Request $request): Response
    {
        $step = (int) $request->input('step', 0);
        $id = $request->input('atleta_id') ? (int) $request->input('atleta_id') : null;
        
        $data = $this->rawInput($request);
        
        $v = $this->validar($data, $id);
        $errors = $v->errors();
        
        // Define fields for each step
        $stepFields = [
            0 => ['nombre', 'apellido', 'cedula', 'telefono', 'fecha_nacimiento', 'sexo', 'pierna_dominante', 'estatus'],
            1 => ['estado_id', 'municipio_id', 'parroquia_id', 'localidad', 'tipo_vivienda', 'ubicacion_vivienda'],
            2 => ['tutor_nombres', 'tutor_apellidos', 'tutor_cedula', 'tutor_telefono', 'tutor_relacion']
        ];
        
        $fieldsToValidate = $stepFields[$step] ?? [];
        $stepErrors = [];
        foreach ($fieldsToValidate as $field) {
            if (isset($errors[$field])) {
                $stepErrors[$field] = $errors[$field];
            }
        }
        
        // Also check if tutor_representante error exists and we are on step 2
        if ($step === 2 && isset($errors['tutor_representante'])) {
            $stepErrors['tutor_representante'] = $errors['tutor_representante'];
        }
        
        if (!empty($stepErrors)) {
            return Response::json([
                'success' => false,
                'errors' => $stepErrors
            ], 422);
        }
        
        return Response::json(['success' => true]);
    }
}
