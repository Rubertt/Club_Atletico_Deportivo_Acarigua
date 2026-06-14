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

        // Si no está el parámetro de paginación en la BD, lo auto-insertamos
        if (!isset($configs['filas_por_pagina'])) {
            try {
                $db->prepare("INSERT INTO configuraciones (clave, valor, descripcion) VALUES ('filas_por_pagina', '15', 'Cantidad de filas por página en las tablas del sistema')")->execute();
                $configs['filas_por_pagina'] = '15';
            } catch (\Throwable) {
                // Silenciamos en caso de que ocurra un error o concurrencia
            }
        }

        // Si no está la edad mínima para atletas en la BD, la auto-insertamos
        if (!isset($configs['edad_minima_atleta'])) {
            try {
                $db->prepare("INSERT INTO configuraciones (clave, valor, descripcion) VALUES ('edad_minima_atleta', '6', 'Edad mínima permitida para registrar un atleta')")->execute();
                $configs['edad_minima_atleta'] = '6';
            } catch (\Throwable) {
                // Silenciamos en caso de que ocurra un error o concurrencia
            }
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
            'filas_por_pagina' => $request->input('filas_por_pagina'),
            'edad_minima_atleta' => $request->input('edad_minima_atleta'),
            'mision' => $request->input('mision'),
            'vision' => $request->input('vision'),
            'requisitos_inscripcion' => $request->input('requisitos_inscripcion'),
            'correo_contacto' => $request->input('correo_contacto'),
            'telefono_whatsapp' => $request->input('telefono_whatsapp'),
            'facebook_url' => $request->input('facebook_url'),
            'instagram_url' => $request->input('instagram_url'),
            'google_maps_url' => $request->input('google_maps_url'),
        ];

        // Validar que al menos tiempo_sesion, filas_por_pagina y edad_minima_atleta sean válidos
        $validator = Validator::make($input, [
            'tiempo_sesion' => 'required|integer',
            'filas_por_pagina' => 'required|integer',
            'edad_minima_atleta' => 'required|integer|min:1|max:25',
            'correo_contacto' => 'email'
        ]);

        if (!$validator->validate()) {
            flash('error', 'Revisa los datos ingresados. Asegúrate de que el tiempo de sesión y las filas por página sean válidos y el correo tenga el formato correcto.');
            return $this->redirect('/admin/configuracion');
        }

        // Filtramos valores nulos por si acaso
        $dataToUpdate = [];
        foreach ($input as $clave => $valor) {
            if ($valor !== null) {
                $dataToUpdate[$clave] = (string) $valor;
            }
        }

        $sessionTimeChanged = (config_db('tiempo_sesion') !== $dataToUpdate['tiempo_sesion']);

        if (Configuracion::updateMany($dataToUpdate)) {
            if ($sessionTimeChanged) {
                flash('success', 'Configuración actualizada exitosamente. El cambio en el tiempo de expiración se aplicará a partir del próximo inicio de sesión.');
            } else {
                flash('success', 'Configuración actualizada exitosamente.');
            }
        } else {
            flash('error', 'Ocurrió un error al guardar la configuración.');
        }

        return $this->redirect('/admin/configuracion');
    }
}
