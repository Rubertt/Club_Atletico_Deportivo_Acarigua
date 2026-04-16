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
use App\Models\Ubicacion;
use App\Services\AtletaService;
use Throwable;

final class AtletasController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'categoria_id' => $request->query('categoria_id'),
            'estatus'      => $request->query('estatus'),
            'q'            => $request->query('q'),
        ];
        $page = max(1, (int) $request->query('page', 1));
        $data = (new Atleta())->paginate(array_filter($filters, fn($v) => $v !== null && $v !== ''), $page, 15);
        $categorias = (new Categoria())->allWithEntrenador();

        return $this->view('atletas.index', [
            'title'      => 'Atletas',
            'active'     => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas'],
            'pag'        => $data,
            'categorias' => $categorias,
            'filters'    => $filters,
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
        return $this->view('atletas.show', [
            'title'      => $atleta['nombre'] . ' ' . $atleta['apellido'],
            'active'     => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', $atleta['nombre'] . ' ' . $atleta['apellido']],
            'atleta'     => $atleta,
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('atletas.form', [
            'title'      => 'Nuevo atleta',
            'active'     => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Nuevo'],
            'atleta'     => null,
            'categorias' => (new Categoria())->activas(),
            'posiciones' => (new PosicionJuego())->all('nombre_posicion'),
            'paises'     => (new Ubicacion())->paises(),
            'action'     => url('/admin/atletas'),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->rawInput($request);
        $errors = $this->validar($data)->errors();
        if ($errors) {
            $this->withOld($data)->withErrors($errors);
            return $this->redirect('/admin/atletas/crear');
        }
        try {
            $service = new AtletaService();
            $id = $service->crear($data, $_FILES['foto'] ?? []);
            flash('success', 'Atleta registrado correctamente.');
            return $this->redirect("/admin/atletas/$id");
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'No se pudo crear el atleta: ' . $e->getMessage());
            $this->withOld($data);
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
            'title'      => 'Editar atleta',
            'active'     => 'atletas',
            'breadcrumb' => ['Inicio', 'Atletas', 'Editar'],
            'atleta'     => $atleta,
            'categorias' => (new Categoria())->activas(),
            'posiciones' => (new PosicionJuego())->all('nombre_posicion'),
            'paises'     => (new Ubicacion())->paises(),
            'action'     => url("/admin/atletas/{$atleta['atleta_id']}"),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $data = $this->rawInput($request);
        $errors = $this->validar($data, $id)->errors();
        if ($errors) {
            $this->withOld($data)->withErrors($errors);
            return $this->redirect("/admin/atletas/$id/editar");
        }
        try {
            (new AtletaService())->actualizar($id, $data, $_FILES['foto'] ?? []);
            flash('success', 'Atleta actualizado.');
            return $this->redirect("/admin/atletas/$id");
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'No se pudo actualizar: ' . $e->getMessage());
            return $this->redirect("/admin/atletas/$id/editar");
        }
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        try {
            (new Atleta())->delete($id);
            Logger::audit('atleta.eliminar', ['atleta_id' => $id]);
            flash('success', 'Atleta eliminado.');
        } catch (Throwable $e) {
            Logger::error($e);
            flash('error', 'No se pudo eliminar (posibles registros asociados).');
        }
        return $this->redirect('/admin/atletas');
    }

    private function rawInput(Request $request): array
    {
        return [
            'nombre'            => trim((string) $request->input('nombre', '')),
            'apellido'          => trim((string) $request->input('apellido', '')),
            'cedula'            => trim((string) $request->input('cedula', '')),
            'telefono'          => trim((string) $request->input('telefono', '')),
            'fecha_nacimiento'  => trim((string) $request->input('fecha_nacimiento', '')),
            'posicion_de_juego' => $request->input('posicion_de_juego') ?: null,
            'pierna_dominante'  => $request->input('pierna_dominante') ?: null,
            'categoria_id'      => $request->input('categoria_id') ?: null,
            'estatus'           => $request->input('estatus', 'Activo'),

            // Dirección
            'parroquia_id'     => $request->input('parroquia_id') ?: null,
            'punto_referencia' => trim((string) $request->input('punto_referencia', '')),
            'calle_avenida'    => trim((string) $request->input('calle_avenida', '')),
            'casa_edificio'    => trim((string) $request->input('casa_edificio', '')),

            // Tutor
            'tutor_nombres'   => trim((string) $request->input('tutor_nombres', '')),
            'tutor_apellidos' => trim((string) $request->input('tutor_apellidos', '')),
            'tutor_cedula'    => trim((string) $request->input('tutor_cedula', '')),
            'tutor_telefono'  => trim((string) $request->input('tutor_telefono', '')),
            'tutor_correo'    => trim((string) $request->input('tutor_correo', '')),
            'tutor_relacion'  => $request->input('tutor_relacion', 'Padre'),

            // Ficha médica
            'alergias'        => trim((string) $request->input('alergias', '')),
            'tipo_sanguineo'  => trim((string) $request->input('tipo_sanguineo', '')),
            'lesion'          => trim((string) $request->input('lesion', '')),
            'condicion_medica' => trim((string) $request->input('condicion_medica', '')),
            'observacion'     => trim((string) $request->input('observacion', '')),
        ];
    }

    private function validar(array $data, ?int $ignoreId = null): Validator
    {
        $rules = [
            'nombre'           => 'required|min:2|max:100',
            'apellido'         => 'required|min:2|max:100',
            'fecha_nacimiento' => 'required|date',
            'estatus'          => 'required|in:Activo,Inactivo,Lesionado,Suspendido',
            'pierna_dominante' => 'in:Derecha,Izquierda,Ambidiestro',
            'tutor_relacion'   => 'in:Padre,Madre,Abuelo/a,Tío/a,Hermano/a,Tutor Legal,Otro',
            'tutor_correo'     => 'email|max:100',
        ];
        $v = Validator::make($data, $rules);
        $v->validate();
        return $v;
    }
}
