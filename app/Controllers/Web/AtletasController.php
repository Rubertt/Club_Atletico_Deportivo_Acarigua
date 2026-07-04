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
        $perPage = (int) config_db('filas_por_pagina', 15);
        $data = $atletaModel->paginate(array_filter($filters, fn($v) => $v !== null && $v !== ''), $page, $perPage);

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

        if ($request->query('ajax') || $request->input('ajax')) {
            return Response::html($this->renderView('atletas.index', [
                'pag' => $data,
                'filters' => $filters,
                'stats' => $stats,
                'categorias' => (new Categoria())->all('nombre_categoria'),
            ]));
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

        // 1. Manejar consultas asíncronas de pestañas individuales (Lazy Loading)
        $tabAjax = $request->query('tab_ajax');
        if ($tabAjax) {
            return $this->renderTabAjax($id, $tabAjax, $atleta);
        }

        // 2. Carga básica (para vista inicial o para refresco parcial por AJAX)
        $asignaciones = (new \App\Models\AsigCategoria())->athleteAssignments($id);
        $paises = (new Direccion())->paises();
        $representantes = (new \App\Models\Representante())->all('nombre, apellido');

        $data = [
            'title' => $atleta['nombre'] . ' ' . $atleta['apellido'],
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', $atleta['nombre'] . ' ' . $atleta['apellido']],
            'atleta' => $atleta,
            'asignaciones' => $asignaciones,
            'paises' => $paises,
            'representantes' => $representantes,
            // Variables diferidas vacías en la carga inicial para evitar lints/errores de vista
            'tipos_discapacidades' => [],
            'medidas_historial' => [],
            'pruebas_historial' => [],
            'asistencias_historial' => [],
            'consultas_historial' => [],
        ];

        // Si es una recarga parcial por AJAX de toda la vista
        if ($request->query('ajax_partial')) {
            return Response::html($this->renderView('atletas.show', $data));
        }

        return $this->view('atletas.show', $data, 'admin');
    }

    private function renderTabAjax(int $id, string $tab, array $atleta): Response
    {
        $pdo = \App\Core\Database::connection();

        switch ($tab) {
            case 'ficha':
                $tipos_discapacidades = $pdo->query("SELECT * FROM tipos_discapacidades ORDER BY nombre_tipo ASC")->fetchAll(\PDO::FETCH_ASSOC);
                return Response::html($this->renderView('atletas.partials.perfil._tab_ficha_medica', [
                    'atleta' => $atleta,
                    'tipos_discapacidades' => $tipos_discapacidades
                ]));

            case 'consulta':
                $consultas_historial = (new \App\Models\ConsultaMedica())->byAtleta($id);
                return Response::html($this->renderView('atletas.partials.perfil._tab_consulta_medica', [
                    'atleta' => $atleta,
                    'consultas_historial' => $consultas_historial
                ]));

            case 'antropometria':
                $medidas_historial = (new MedidaAntropometrica())->historial($id);
                return Response::html($this->renderView('atletas.partials.perfil._tab_antropometria', [
                    'atleta' => $atleta,
                    'medidas_historial' => $medidas_historial
                ]));

            case 'pruebas':
                $pruebas_historial = (new ResultadoPrueba())->historial($id);
                return Response::html($this->renderView('atletas.partials.perfil._tab_pruebas', [
                    'pruebas_historial' => $pruebas_historial
                ]));

            case 'asistencia':
                $asistencias_historial = (new Asistencia())->historialAtleta($id);
                return Response::html($this->renderView('atletas.partials.perfil._tab_asistencia', [
                    'asistencias_historial' => $asistencias_historial
                ]));

            default:
                return Response::html('<div class="alert alert-danger">Pestaña no válida.</div>');
        }
    }

    public function create(Request $request): Response
    {
        $representantes = (new \App\Models\Representante())->all('nombre, apellido');
        $direcciones = (new \App\Models\Direccion())->query(
            'SELECT d.*, p.parroquia, m.municipio, e.estado, p.municipio_id, m.estado_id
             FROM direcciones d
             JOIN parroquias p ON d.parroquias_id = p.parroquia_id
             JOIN municipios m ON p.municipio_id = m.municipio_id
             JOIN estados e ON m.estado_id = e.estado_id
             ORDER BY e.estado, m.municipio, p.parroquia, d.localidad'
        );

        return $this->view('atletas.form', [
            'title' => 'Nuevo atleta',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Nuevo'],
            'atleta' => null,
            'paises' => (new Direccion())->paises(),
            'representantes' => $representantes,
            'direcciones' => $direcciones,
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
        $representantes = (new \App\Models\Representante())->all('nombre, apellido');
        $direcciones = (new \App\Models\Direccion())->query(
            'SELECT d.*, p.parroquia, m.municipio, e.estado, p.municipio_id, m.estado_id
             FROM direcciones d
             JOIN parroquias p ON d.parroquias_id = p.parroquia_id
             JOIN municipios m ON p.municipio_id = m.municipio_id
             JOIN estados e ON m.estado_id = e.estado_id
             ORDER BY e.estado, m.municipio, p.parroquia, d.localidad'
        );

        return $this->view('atletas.form', [
            'title' => 'Editar atleta',
            'active' => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Editar'],
            'atleta' => $atleta,
            'paises' => (new Direccion())->paises(),
            'representantes' => $representantes,
            'direcciones' => $direcciones,
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
        return clean_cedula_dots($cedula);
    }

    private function mergeData(array $actual, Request $request): array
    {
        $crearNuevoRep = $request->input('crear_nuevo_representante') === '1';
        $representanteIdIn = $request->input('representante_id');
        if ($crearNuevoRep) {
            $repId = null;
        } elseif ($request->input('representante_id') !== null) {
            $repId = $representanteIdIn ? (int) $representanteIdIn : null;
        } else {
            $repId = $actual['representante_id'] ?? null;
        }

        $input = [
            'nombre'            => $request->input('nombre', $actual['nombre']),
            'apellido'          => $request->input('apellido', $actual['apellido']),
            'cedula'            => $request->input('cedula') !== null ? ($request->input('cedula') ? $this->cleanCedulaDots($request->input('cedula')) : null) : $this->cleanCedulaDots($actual['cedula']),
            'sexo'              => $request->input('sexo', $actual['sexo']),
            'telefono'          => $request->input('telefono') !== null ? ($request->input('telefono') ?: null) : $actual['telefono'],
            'fecha_nacimiento'  => $request->input('fecha_nacimiento', $actual['fecha_nac']),
            'pierna_dominante'  => $request->input('pierna_dominante') !== null ? ($request->input('pierna_dominante') ?: null) : $actual['pierna_dominante'],
            'estatus'           => $request->input('estatus') !== null ? (int) $request->input('estatus') : $actual['estatus'],
            'representante_id'  => $repId,
            'crear_nuevo_representante' => $crearNuevoRep,
            'direccion_id'      => $request->input('direccion_id', $actual['direccion_id'] ?? null),
            
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
            'representante_id' => $request->input('representante_id') ? (int) $request->input('representante_id') : null,
            'direccion_id' => $request->input('direccion_id') ? (int) $request->input('direccion_id') : null,

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
        // Regex: documento de identidad venezolano V-NUMERO o E-NUMERO (6 a 8 dígitos) o N-FECHA-NUMERO-FOLIO (acta de nacimiento) o P-NUMERO (pasaporte alfanumérico 5 a 15)
        $cedRegex = '/^([VE]-\d{6,8}|N-\d{4}-[A-Z0-9]{1,6}-[A-Z0-9]{1,3}|P-[A-Z0-9]{5,15})$/i';
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

        // 1. Documento de Identidad obligatorio si es mayor de 9 años
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
        $isRepresentanteModal = $tieneRepresentanteEnPost && !isset($_POST['representante_id']);

        if ($age < 18 || $tieneRepresentanteEnPost) {
            $seSeleccionoExistente = !empty($_POST['representante_id']);

            if ($seSeleccionoExistente && !$isRepresentanteModal) {
                $rules['representante_id'] = 'integer';
            } else {
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
            }
        } else {
            // Si es mayor de edad y no se envía el modal de representante, es opcional pero se valida formato si existe
            if (!empty($data['representante_id'])) {
                $rules['representante_id'] = 'integer';
            } else {
                if (!empty($data['tutor_cedula'])) {
                    $rules['tutor_cedula'] = ["regex:$cedRegex"];
                }
                if (!empty($data['tutor_telefono'])) {
                    $rules['tutor_telefono'] = ["regex:$telRegex"];
                }
            }
        }

        // 4. Validar dirección detallada si estamos en registro o si se envían datos de dirección en el request
        $esRegistro = ($ignoreId === null);
        $tieneDireccionEnRequest = isset($_POST['parroquia_id']) || isset($_POST['localidad']);
        $isDireccionModal = $tieneDireccionEnRequest && !isset($_POST['direccion_id']);

        if ($esRegistro || $tieneDireccionEnRequest) {
            $seSeleccionoDireccionExistente = !empty($_POST['direccion_id']);

            if ($seSeleccionoDireccionExistente && !$isDireccionModal) {
                $rules['direccion_id'] = 'integer';
            } else {
                $rules['parroquia_id'] = 'required|integer';
                $rules['localidad'] = 'required|min:2|max:200';
                $rules['tipo_vivienda'] = 'required|in:casa,apto,edificio';
                $rules['ubicacion_vivienda'] = 'required|min:2|max:500';
            }
        }

        $messages = [
            'cedula' => 'El documento de identidad del atleta ya está registrado o tiene un formato inválido (Ej: V-12345678, E-12345678 (6 a 8 dígitos, sin puntos), N-AÑO-ACTA o P-Pasaporte). Es obligatorio para mayores de 9 años y debe ser único.',
            'telefono' => 'El teléfono debe comenzar con 0412, 0414, 0416, 0422, 0424, 0255 o 0256 and tener 11 dígitos. Es obligatorio para mayores de edad.',
            'tutor_representante' => 'Para registrar al atleta como menor de edad, primero debe asignar y guardar los datos de su representante en la sección correspondiente de su perfil.',
            'tutor_nombres' => 'El nombre del representante es obligatorio.',
            'tutor_apellidos' => 'El apellido del representante es obligatorio.',
            'tutor_cedula' => 'El documento de identidad del representante es obligatorio y debe tener un formato válido (Ej: V-12345678, E-12345678 (6 a 8 dígitos, sin puntos) o P-Pasaporte).',
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
                    $edadMinima = (int) config_db('edad_minima_atleta', self::EDAD_MINIMA_ATLETA);
                    if ($age < $edadMinima) {
                        $v->addError('fecha_nacimiento', 'El atleta debe tener al menos ' . $edadMinima . ' años de edad.');
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

        // Validar duplicados globales (documento de identidad del atleta) en representantes y usuarios
        if (!empty($data['cedula'])) {
            $existsInUsuarios = (new \App\Models\Usuario())->queryOne(
                'SELECT 1 FROM usuarios WHERE cedula = :c LIMIT 1',
                [':c' => $data['cedula']]
            );
            if ($existsInUsuarios) {
                $v->addError('cedula', 'Error: El número de documento de identidad ingresado ya existe en la tabla de usuarios.');
            }

            $existsInRepresentantes = (new \App\Models\Representante())->queryOne(
                'SELECT 1 FROM representantes WHERE cedula = :c LIMIT 1',
                [':c' => $data['cedula']]
            );
            if ($existsInRepresentantes) {
                $v->addError('cedula', 'Error: El número de documento de identidad ingresado ya existe en la tabla de representantes.');
            }
        }

        // Validar duplicados globales (documento de identidad del representante) en atletas y usuarios
        if (!empty($data['tutor_cedula'])) {
            $existsInAtletas = (new \App\Models\Atleta())->queryOne(
                'SELECT 1 FROM atletas WHERE cedula = :c' . ($ignoreId ? ' AND atleta_id <> :ignore' : '') . ' LIMIT 1',
                $ignoreId ? [':c' => $data['tutor_cedula'], ':ignore' => $ignoreId] : [':c' => $data['tutor_cedula']]
            );
            if ($existsInAtletas) {
                $v->addError('tutor_cedula', 'Error: El número de documento de identidad del representante ya existe en la tabla de atletas.');
            }

            $existsInUsuarios = (new \App\Models\Usuario())->queryOne(
                'SELECT 1 FROM usuarios WHERE cedula = :c LIMIT 1',
                [':c' => $data['tutor_cedula']]
            );
            if ($existsInUsuarios) {
                $v->addError('tutor_cedula', 'Error: El número de documento de identidad del representante ya existe en la tabla de usuarios.');
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
            1 => ['estado_id', 'municipio_id', 'parroquia_id', 'localidad', 'tipo_vivienda', 'ubicacion_vivienda', 'direccion_id'],
            2 => ['tutor_nombres', 'tutor_apellidos', 'tutor_cedula', 'tutor_telefono', 'tutor_relacion', 'representante_id']
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
