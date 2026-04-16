<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Plantel;
use App\Models\Rol;

final class PlantelController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('plantel.index', [
            'title' => 'Plantel',
            'active' => 'plantel',
            'breadcrumb' => ['Inicio', 'Plantel'],
            'items' => (new Plantel())->allWithRol(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->view('plantel.form', [
            'title' => 'Nuevo miembro del plantel',
            'active' => 'plantel',
            'breadcrumb' => ['Inicio', 'Plantel', 'Nuevo'],
            'item' => null,
            'roles' => (new Rol())->all(),
            'action' => url('/admin/plantel'),
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $this->input($request);
        $v = Validator::make($data, [
            'nombre'    => 'required|min:2|max:100',
            'apellido'  => 'required|min:2|max:100',
            'telefono'  => 'required|min:7|max:20',
            'rol_id'    => 'required|integer',
            'cedula'    => 'max:20',
        ]);
        if (!$v->validate()) {
            $this->withOld($data)->withErrors($v->errors());
            return $this->redirect('/admin/plantel/crear');
        }
        (new Plantel())->insert($data);
        flash('success', 'Miembro del plantel registrado.');
        return $this->redirect('/admin/plantel');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $item = (new Plantel())->find($id);
        if (!$item) { flash('error', 'No encontrado.'); return $this->redirect('/admin/plantel'); }
        return $this->view('plantel.form', [
            'title' => 'Editar plantel',
            'active' => 'plantel',
            'breadcrumb' => ['Inicio', 'Plantel', 'Editar'],
            'item' => $item,
            'roles' => (new Rol())->all(),
            'action' => url("/admin/plantel/$id"),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        (new Plantel())->update($id, $this->input($request));
        flash('success', 'Plantel actualizado.');
        return $this->redirect('/admin/plantel');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        try {
            (new Plantel())->delete($id);
            flash('success', 'Eliminado.');
        } catch (\Throwable $e) {
            flash('error', 'No se pudo eliminar (tiene categorías o eventos asociados).');
        }
        return $this->redirect('/admin/plantel');
    }

    private function input(Request $request): array
    {
        return [
            'nombre'    => trim((string) $request->input('nombre')),
            'apellido'  => trim((string) $request->input('apellido')),
            'cedula'    => $request->input('cedula') ?: null,
            'telefono'  => trim((string) $request->input('telefono')),
            'fecha_nac' => $request->input('fecha_nac') ?: null,
            'rol_id'    => (int) $request->input('rol_id'),
        ];
    }
}
