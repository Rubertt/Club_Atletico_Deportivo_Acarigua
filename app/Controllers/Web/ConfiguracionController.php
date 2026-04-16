<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Rol;
use App\Models\Usuario;

final class ConfiguracionController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('configuracion.index', [
            'title' => 'Configuración',
            'active' => 'configuracion',
            'breadcrumb' => ['Inicio', 'Configuración'],
        ], 'admin');
    }

    public function usuarios(Request $request): Response
    {
        return $this->view('configuracion.usuarios', [
            'title' => 'Usuarios del sistema',
            'active' => 'configuracion',
            'breadcrumb' => ['Inicio', 'Configuración', 'Usuarios'],
            'usuarios' => (new Usuario())->allWithRol(),
            'roles'    => (new Rol())->all(),
        ], 'admin');
    }

    public function guardarUsuario(Request $request): Response
    {
        $data = [
            'email'    => trim((string) $request->input('email')),
            'password' => (string) $request->input('password'),
            'rol_id'   => (int) $request->input('rol_id'),
            'estatus'  => $request->input('estatus', 'Activo'),
        ];

        $v = Validator::make($data, [
            'email'    => 'required|email|max:100',
            'password' => 'required|min:8|max:255',
            'rol_id'   => 'required|integer',
            'estatus'  => 'required|in:Activo,Inactivo',
        ]);
        if (!$v->validate()) {
            $this->withOld($data)->withErrors($v->errors());
            return $this->redirect('/admin/configuracion/usuarios');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        (new Usuario())->insert($data);
        flash('success', 'Usuario creado.');
        return $this->redirect('/admin/configuracion/usuarios');
    }
}
