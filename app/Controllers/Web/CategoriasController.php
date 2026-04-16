<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Categoria;
use App\Models\Plantel;

final class CategoriasController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('categorias.index', [
            'title' => 'Categorías',
            'active' => 'categorias',
            'breadcrumb' => ['Inicio', 'Categorías'],
            'items' => (new Categoria())->allWithEntrenador(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('categorias.form', [
            'title' => 'Nueva categoría',
            'active' => 'categorias',
            'breadcrumb' => ['Inicio', 'Categorías', 'Nueva'],
            'item' => null,
            'entrenadores' => (new Plantel())->entrenadores(),
            'action' => url('/admin/categorias'),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->input($request);
        $v = Validator::make($data, [
            'nombre_categoria' => 'required|min:2|max:50',
            'edad_min'         => 'required|integer|min:3|max:100',
            'edad_max'         => 'required|integer|min:3|max:100',
            'estatus'          => 'required|in:Activa,Inactiva',
        ]);
        if (!$v->validate()) {
            $this->withOld($data)->withErrors($v->errors());
            return $this->redirect('/admin/categorias/crear');
        }
        (new Categoria())->insert($data);
        flash('success', 'Categoría creada.');
        return $this->redirect('/admin/categorias');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $item = (new Categoria())->find($id);
        if (!$item) { flash('error', 'No encontrada.'); return $this->redirect('/admin/categorias'); }
        return $this->view('categorias.form', [
            'title' => 'Editar categoría',
            'active' => 'categorias',
            'breadcrumb' => ['Inicio', 'Categorías', 'Editar'],
            'item' => $item,
            'entrenadores' => (new Plantel())->entrenadores(),
            'action' => url("/admin/categorias/$id"),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $data = $this->input($request);
        (new Categoria())->update($id, $data);
        flash('success', 'Categoría actualizada.');
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
            'entrenador_id'    => $request->input('entrenador_id') ?: null,
            'estatus'          => $request->input('estatus', 'Activa'),
        ];
    }
}
