<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Categoria;
use App\Models\Usuario;

final class CategoriasController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'sexo' => trim((string) $request->input('sexo', '')),
            'entrenador_id' => trim((string) $request->input('entrenador_id', '')),
        ];

        return $this->view('categorias.index', [
            'title' => 'Categorías',
            'active' => 'categorias',
            'breadcrumb' => ['Inicio', 'Categorías'],
            'items' => (new Categoria())->allWithEntrenador($filters),
            'filters' => $filters,
            'entrenadores' => (new Usuario())->coordinadoresCategorias(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('categorias.form', [
            'title' => 'Nueva categoría',
            'active' => 'categorias',
            'breadcrumb' => ['Inicio', 'Categorías', 'Nueva'],
            'item' => null,
            'entrenadores' => (new Usuario())->coordinadoresCategorias(),
            'action' => url('/admin/categorias'),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->input($request);

        // Auto-generación del nombre de la categoría
        $sexo = $data['sexo_categoria'];
        $sexSuffix = match ($sexo) {
            'M' => '(m)',
            'F' => '(f)',
            'X' => '(mix)',
            default => ''
        };
        $data['nombre_categoria'] = 'sub-' . $data['edad_max'] . $sexSuffix;

        $v = Validator::make($data, [
            'nombre_categoria' => 'required|min:2|max:50',
            'edad_min'         => 'required|integer|min:6|max:100',
            'edad_max'         => 'required|integer|min:6|max:100',
            'sexo_categoria'   => 'required|in:M,F,X',
            'usuario_id'       => 'required|integer',
            'estatus'          => 'required|in:1,2',
        ], [
            'nombre_categoria' => 'El nombre de la categoría es obligatorio y debe tener al menos 2 caracteres.',
            'edad_min'         => 'La edad mínima es obligatoria y debe ser de al menos 6 años.',
            'edad_max'         => 'La edad máxima es obligatoria y debe ser de al menos 6 años.',
            'sexo_categoria'   => 'El género de la categoría es obligatorio.',
            'usuario_id'       => 'El enlistador es obligatorio.',
            'estatus'          => 'El estatus es obligatorio.',
        ]);
        if (!$v->validate()) {
            $this->withOld($data)->withErrors($v->errors());
            return $this->redirect('/admin/categorias/crear');
        }
        if ($data['edad_min'] >= $data['edad_max']) {
            $this->withOld($data)->withErrors(['edad_min' => 'La edad mínima debe ser estrictamente menor que la edad máxima.']);
            return $this->redirect('/admin/categorias/crear');
        }

        $entrenador = (new Usuario())->find((int) $data['usuario_id']);
        $rolId = $entrenador ? (int) $entrenador['rol_id'] : 0;
        if (!$entrenador || ($rolId !== ROL_ENTRENADOR && $rolId !== ROL_DIRECTIVO)) {
            $this->withOld($data)->withErrors(['usuario_id' => 'El enlistador seleccionado no es válido o no posee el rol requerido.']);
            return $this->redirect('/admin/categorias/crear');
        }

        try {
            (new Categoria())->insert($data);
            flash('success', 'Categoría creada.');
        } catch (\Throwable $e) {
            // Duplicate key (nombre_categoria + sexo_categoria)
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), '1062')) {
                flash('error', 'Ya existe una categoría con ese nombre y género.');
                $this->withOld($data);
                return $this->redirect('/admin/categorias/crear');
            }
            throw $e;
        }
        return $this->redirect('/admin/categorias');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $item = (new Categoria())->find($id);
        if (!$item) { flash('error', 'No encontrada.'); return $this->redirect('/admin/categorias'); }

        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM asig_categorias WHERE categoria_id = ?");
        $stmt->execute([$id]);
        $item['total_atletas'] = (int) $stmt->fetchColumn();

        return $this->view('categorias.form', [
            'title' => 'Editar categoría',
            'active' => 'categorias',
            'breadcrumb' => ['Inicio', 'Categorías', 'Editar'],
            'item' => $item,
            'entrenadores' => (new Usuario())->coordinadoresCategorias(),
            'action' => url("/admin/categorias/$id"),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $categoriaModel = new Categoria();
        $original = $categoriaModel->find($id);

        if (!$original) {
            flash('error', 'Categoría no encontrada.');
            return $this->redirect('/admin/categorias');
        }

        $data = $this->input($request);

        // Validar si tiene atletas asignados
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM asig_categorias WHERE categoria_id = ?");
        $stmt->execute([$id]);
        $totalAtletas = (int) $stmt->fetchColumn();

        if ($totalAtletas > 0) {
            $errors = [];
            if ($original['sexo_categoria'] !== $data['sexo_categoria']) {
                $errors['sexo_categoria'] = 'No se puede modificar el género de una categoría con atletas asignados.';
            }
            if ((int) $original['edad_min'] !== $data['edad_min']) {
                $errors['edad_min'] = 'No se puede modificar la edad mínima de una categoría con atletas asignados.';
            }
            if ((int) $original['edad_max'] !== $data['edad_max']) {
                $errors['edad_max'] = 'No se puede modificar la edad máxima de una categoría con atletas asignados.';
            }

            if (!empty($errors)) {
                $this->withOld($data)->withErrors($errors);
                return $this->redirect("/admin/categorias/$id/editar");
            }
        }

        // Auto-generación del nombre de la categoría
        $sexo = $data['sexo_categoria'];
        $sexSuffix = match ($sexo) {
            'M' => '(m)',
            'F' => '(f)',
            'X' => '(mix)',
            default => ''
        };
        $data['nombre_categoria'] = 'sub-' . $data['edad_max'] . $sexSuffix;

        $v = Validator::make($data, [
            'nombre_categoria' => 'required|min:2|max:50',
            'edad_min'         => 'required|integer|min:6|max:100',
            'edad_max'         => 'required|integer|min:6|max:100',
            'sexo_categoria'   => 'required|in:M,F,X',
            'usuario_id'       => 'required|integer',
            'estatus'          => 'required|in:1,2',
        ], [
            'nombre_categoria' => 'El nombre de la categoría es obligatorio y debe tener al menos 2 caracteres.',
            'edad_min'         => 'La edad mínima es obligatoria y debe ser de al menos 6 años.',
            'edad_max'         => 'La edad máxima es obligatoria y debe ser de al menos 6 años.',
            'sexo_categoria'   => 'El género de la categoría es obligatorio.',
            'usuario_id'       => 'El enlistador es obligatorio.',
            'estatus'          => 'El estatus es obligatorio.',
        ]);
        if (!$v->validate()) {
            $this->withOld($data)->withErrors($v->errors());
            return $this->redirect("/admin/categorias/$id/editar");
        }
        if ($data['edad_min'] >= $data['edad_max']) {
            $this->withOld($data)->withErrors(['edad_min' => 'La edad mínima debe ser estrictamente menor que la edad máxima.']);
            return $this->redirect("/admin/categorias/$id/editar");
        }

        $entrenador = (new Usuario())->find((int) $data['usuario_id']);
        $rolId = $entrenador ? (int) $entrenador['rol_id'] : 0;
        if (!$entrenador || ($rolId !== ROL_ENTRENADOR && $rolId !== ROL_DIRECTIVO)) {
            $this->withOld($data)->withErrors(['usuario_id' => 'El enlistador seleccionado no es válido o no posee el rol de entrenador o directivo.']);
            return $this->redirect("/admin/categorias/$id/editar");
        }

        // Si intenta inactivar (estatus 2), verificar si tiene atletas
        if ($data['estatus'] === 2) {
            if ($totalAtletas > 0) {
                $this->withOld($data)->withErrors(['estatus' => 'No se puede inactivar una categoría que tiene atletas asignados.']);
                return $this->redirect("/admin/categorias/$id/editar");
            }
        }

        try {
            (new Categoria())->update($id, $data);
            flash('success', 'Categoría actualizada.');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), '1062')) {
                flash('error', 'Ya existe una categoría con ese nombre y género.');
                $this->withOld($data);
                return $this->redirect("/admin/categorias/$id/editar");
            }
            throw $e;
        }
        return $this->redirect('/admin/categorias');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        try {
            (new Categoria())->delete($id);
            flash('success', 'Categoría eliminada.');
        } catch (\Throwable $e) {
            flash('error', 'No se pudo eliminar (tiene atletas asignados).');
        }
        return $this->redirect('/admin/categorias');
    }

    private function input(Request $request): array
    {
        return [
            'nombre_categoria' => trim((string) $request->input('nombre_categoria')),
            'edad_min'         => (int) $request->input('edad_min', 0),
            'edad_max'         => (int) $request->input('edad_max', 0),
            'usuario_id'       => $request->input('usuario_id') ? (int) $request->input('usuario_id') : null,
            'sexo_categoria'   => strtoupper((string) $request->input('sexo_categoria', 'M')),
            'estatus'          => (int) $request->input('estatus', 1),
        ];
    }
}
