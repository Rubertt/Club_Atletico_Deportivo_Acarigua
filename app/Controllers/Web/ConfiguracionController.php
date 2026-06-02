<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Configuracion;

final class ConfiguracionController extends Controller
{
    public function index(Request $request): Response
    {
        // Forzamos cargar en memoria si no se han cargado y obtenemos todo. 
        // Como 'config_db' carga el caché interno, pero necesitamos todas,
        // vamos a obtenerlas desde la BD directamente para la vista.
        $db = \App\Core\Database::connection();
        $rows = $db->query('SELECT clave, valor FROM configuraciones')->fetchAll();
        $configs = [];
        foreach ($rows as $row) {
            $configs[$row['clave']] = $row['valor'];
        }

        return $this->view('configuracion.index', [
            'title' => 'Configuración General',
            'active' => 'configuracion',
            'breadcrumb' => ['Inicio', 'Configuración'],
            'configs' => $configs
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $input = [
            'tiempo_sesion' => $request->input('tiempo_sesion'),
            'mision' => $request->input('mision'),
            'vision' => $request->input('vision'),
            'requisitos_inscripcion' => $request->input('requisitos_inscripcion'),
            'correo_contacto' => $request->input('correo_contacto'),
            'telefono_whatsapp' => $request->input('telefono_whatsapp'),
            'facebook_url' => $request->input('facebook_url'),
            'instagram_url' => $request->input('instagram_url'),
            'google_maps_url' => $request->input('google_maps_url'),
        ];

        // Validar que al menos tiempo_sesion sea un número válido
        $validator = Validator::make($input, [
            'tiempo_sesion' => 'required|integer',
            'correo_contacto' => 'email'
        ]);

        if (!$validator->validate()) {
            flash('error', 'Revisa los datos ingresados. Asegúrate de que el tiempo de sesión sea válido y el correo tenga el formato correcto.');
            return $this->redirect('/admin/configuracion');
        }

        // Filtramos valores nulos por si acaso
        $dataToUpdate = [];
        foreach ($input as $clave => $valor) {
            if ($valor !== null) {
                $dataToUpdate[$clave] = (string) $valor;
            }
        }

        if (Configuracion::updateMany($dataToUpdate)) {
            flash('success', 'Configuración actualizada exitosamente. El cambio en el tiempo de expiración se aplicará a partir del próximo inicio de sesión.');
        } else {
            flash('error', 'Ocurrió un error al guardar la configuración.');
        }

        return $this->redirect('/admin/configuracion');
    }
}
