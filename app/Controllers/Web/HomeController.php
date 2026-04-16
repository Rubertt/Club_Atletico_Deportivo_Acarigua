<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Logger;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('public.home', [
            'title'  => config('app.name') . ' - Inicio',
            'active' => 'home',
        ], 'public');
    }

    public function nosotros(Request $request): Response
    {
        return $this->view('public.nosotros', [
            'title'  => 'Nosotros - ' . config('app.name'),
            'active' => 'nosotros',
        ], 'public');
    }

    public function contacto(Request $request): Response
    {
        return $this->view('public.contacto', [
            'title'  => 'Contacto - ' . config('app.name'),
            'active' => 'contacto',
        ], 'public');
    }

    public function enviarContacto(Request $request): Response
    {
        $input = [
            'nombre'  => trim((string) $request->input('nombre', '')),
            'correo'  => trim((string) $request->input('correo', '')),
            'mensaje' => trim((string) $request->input('mensaje', '')),
        ];

        $validator = Validator::make($input, [
            'nombre'  => 'required|min:3|max:100',
            'correo'  => 'required|email|max:100',
            'mensaje' => 'required|min:10|max:1000',
        ]);

        if (!$validator->validate()) {
            $this->withOld($input)->withErrors($validator->errors());
            flash('error', 'Revisa los campos del formulario.');
            return $this->redirect('/contacto');
        }

        Logger::info('contacto.recibido', $input + ['ip' => $request->ip()]);
        flash('success', '¡Gracias! Recibimos tu mensaje y te responderemos pronto.');
        return $this->redirect('/contacto');
    }
}
