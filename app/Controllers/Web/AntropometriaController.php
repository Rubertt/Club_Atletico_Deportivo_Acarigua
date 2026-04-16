<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Atleta;
use App\Models\MedidasAntropometricas;

final class AntropometriaController extends Controller
{
    public function index(Request $request): Response
    {
        $pag = (new Atleta())->paginate(['estatus' => 'Activo'], (int) $request->query('page', 1), 20);
        return $this->view('antropometria.index', [
            'title' => 'Antropometría',
            'active' => 'antropometria',
            'breadcrumb' => ['Inicio', 'Reportes', 'Antropometría'],
            'pag' => $pag,
        ], 'admin');
    }

    public function atleta(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) { flash('error', 'Atleta no encontrado.'); return $this->redirect('/admin/antropometria'); }
        $historial = (new MedidasAntropometricas())->historial($id);
        return $this->view('antropometria.atleta', [
            'title' => 'Antropometría - ' . $atleta['nombre'] . ' ' . $atleta['apellido'],
            'active' => 'antropometria',
            'breadcrumb' => ['Inicio', 'Antropometría', $atleta['nombre']],
            'atleta' => $atleta,
            'historial' => $historial,
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $id = (int) $request->param('id');
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
        ]);
        if (!$v->validate()) {
            $this->withErrors($v->errors());
            return $this->redirect("/admin/antropometria/atleta/$id");
        }
        (new MedidasAntropometricas())->insert($data);
        flash('success', 'Medición registrada.');
        return $this->redirect("/admin/antropometria/atleta/$id");
    }
}
