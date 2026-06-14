<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Usuario;
use App\Models\Direccion;
use App\Models\PreguntaSeguridad;
use App\Models\RespuestaSeguridad;

final class PerfilController extends Controller
{
    // ──────────────────────────────────────────────
    // Setup inicial (primer login)
    // ──────────────────────────────────────────────
    public function setup(Request $request): Response
    {
        if (empty($_SESSION['must_change_password'])) {
            return $this->redirect('/admin');
        }

        $preguntas = (new PreguntaSeguridad())->all();
        
        return $this->view('perfil.setup', [
            'title' => 'Configuración de Seguridad',
            'preguntas' => $preguntas,
            'hideHeader' => true
        ], 'public'); 
    }

    public function saveSetup(Request $request): Response
    {
        if (empty($_SESSION['must_change_password'])) {
            return $this->redirect('/admin');
        }

        $user = Auth::user();
        $data = [
            'password' => $request->input('password', ''),
            'password_confirm' => $request->input('password_confirm', ''),
            'pregunta_1' => $request->input('pregunta_1', ''),
            'respuesta_1' => $request->input('respuesta_1', ''),
            'pregunta_2' => $request->input('pregunta_2', ''),
            'respuesta_2' => $request->input('respuesta_2', ''),
        ];

        $v = Validator::make($data, [
            'password' => 'required|min:8|max:50',
            'password_confirm' => 'required',
            'pregunta_1' => 'required|integer',
            'respuesta_1' => 'required|min:3',
            'pregunta_2' => 'required|integer',
            'respuesta_2' => 'required|min:3',
        ], [
            'password' => 'La contraseña debe tener al menos 8 caracteres.',
            'pregunta_1' => 'Debes seleccionar una pregunta.',
            'pregunta_2' => 'Debes seleccionar una pregunta diferente.',
            'respuesta_1' => 'La respuesta es muy corta.',
            'respuesta_2' => 'La respuesta es muy corta.'
        ]);

        if (!$v->validate()) {
            $this->withErrors($v->errors());
            return $this->redirect('/admin/setup');
        }

        if (!preg_match('/[A-Za-z]/', $data['password']) || !preg_match('/[0-9]/', $data['password']) || !preg_match('/[^A-Za-z0-9]/', $data['password'])) {
            $this->withErrors(['password' => 'La contraseña debe tener al menos una letra, un número y un símbolo.']);
            return $this->redirect('/admin/setup');
        }

        if ($data['password'] !== $data['password_confirm']) {
            $this->withErrors(['password_confirm' => 'Las contraseñas no coinciden.']);
            return $this->redirect('/admin/setup');
        }

        if ($data['pregunta_1'] === $data['pregunta_2']) {
            $this->withErrors(['pregunta_2' => 'Debes seleccionar dos preguntas diferentes.']);
            return $this->redirect('/admin/setup');
        }

        $pwdNum = preg_replace('/[^0-9]/', '', $data['password']);
        $cedNum = preg_replace('/[^0-9]/', '', (string)$user['cedula']);

        if ($cedNum !== '' && $pwdNum === $cedNum) {
            $this->withErrors(['password' => 'La nueva contraseña no puede ser tu número de cédula (incluso si le agregas letras o puntos). Usa una contraseña distinta.']);
            return $this->redirect('/admin/setup');
        }

        $usuarioModel = new Usuario();
        $usuarioModel->update((int) $user['usuario_id'], [
            'contrasena' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12])
        ]);

        $respModel = new RespuestaSeguridad();
        $respModel->deleteByUser((int) $user['usuario_id']); // Limpiar intentos fallidos previos
        $respModel->saveRespuesta((int) $user['usuario_id'], (int) $data['pregunta_1'], $data['respuesta_1']);
        $respModel->saveRespuesta((int) $user['usuario_id'], (int) $data['pregunta_2'], $data['respuesta_2']);

        unset($_SESSION['must_change_password']);
        flash('success', '¡Cuenta configurada con éxito! Bienvenido al sistema.');
        
        return $this->redirect('/admin');
    }

    // ──────────────────────────────────────────────
    // Mi Perfil
    // ──────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $userId = (int) $user['usuario_id'];

        $usuarioModel = new Usuario();
        $item = $usuarioModel->findCompleto($userId);

        $preguntas = (new PreguntaSeguridad())->all();
        $respuestas = (new RespuestaSeguridad())->getByUser($userId);

        return $this->view('perfil.index', [
            'title'       => 'Mi Perfil',
            'active'      => 'perfil',
            'breadcrumb'  => ['Inicio', 'Mi Perfil'],
            'item'        => $item,
            'preguntas'   => $preguntas,
            'respuestas'  => $respuestas,
        ], 'admin');
    }

    public function updatePerfil(Request $request): Response
    {
        $user = Auth::user();
        $userId = (int) $user['usuario_id'];

        $data = [
            'correo'   => trim((string) $request->input('correo')),
            'telefono' => trim((string) $request->input('telefono')),
            'parroquia_id' => $request->input('parroquia_id'),
            'localidad' => trim((string) $request->input('localidad', '')),
            'tipo_vivienda' => trim((string) $request->input('tipo_vivienda', '')),
            'ubicacion_vivienda' => trim((string) $request->input('ubicacion_vivienda', '')),
        ];

        $v = Validator::make($data, [
            'correo'   => 'required|email|max:50',
            'telefono' => 'required|max:15',
            'parroquia_id' => 'required|integer',
            'localidad' => 'required|max:100',
            'tipo_vivienda' => 'required',
            'ubicacion_vivienda' => 'required|max:100',
        ]);

        if (!$v->validate()) {
            $this->withOld($data)->withErrors($v->errors());
            return $this->redirect('/admin/perfil');
        }

        $usuarioModel = new Usuario();

        // Verificar que el correo no esté en uso por otro usuario
        $db = Database::connection();
        $stmt = $db->prepare('SELECT usuario_id FROM usuarios WHERE correo = :correo AND usuario_id != :id LIMIT 1');
        $stmt->execute([':correo' => $data['correo'], ':id' => $userId]);
        if ($stmt->fetch()) {
            $this->withOld($data)->withErrors(['correo' => 'Este correo electrónico ya está en uso por otro usuario.']);
            return $this->redirect('/admin/perfil');
        }
        $actual = $usuarioModel->find($userId);

        // Dirección
        $dirData = [
            'parroquias_id'      => (int) $request->input('parroquia_id'),
            'localidad'          => trim((string) $request->input('localidad', '')),
            'tipo_vivienda'      => trim((string) $request->input('tipo_vivienda', '')),
            'ubicacion_vivienda' => trim((string) $request->input('ubicacion_vivienda', '')),
        ];

        $dirModel = new Direccion();
        if (!empty($actual['direccion_id'])) {
            $dirModel->update((int) $actual['direccion_id'], $dirData);
            $direccionId = (int) $actual['direccion_id'];
        } else {
            $direccionId = $dirModel->insert($dirData);
        }

        $updateData = [
            'correo'       => $data['correo'],
            'telefono'     => $data['telefono'],
            'direccion_id' => $direccionId,
        ];
        // Foto de perfil
        $fotoFile = $request->file('foto');
        if ($request->input('eliminar_foto') === '1') {
            if (!empty($actual['foto'])) {
                $oldFile = BASE_PATH . '/public/' . $actual['foto'];
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $updateData['foto'] = null;
        } elseif ($fotoFile && $fotoFile['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($fotoFile['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $dest = BASE_PATH . '/public/uploads/usuarios/' . $filename;
                if (!is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0755, true);
                }
                move_uploaded_file($fotoFile['tmp_name'], $dest);
                $updateData['foto'] = 'uploads/usuarios/' . $filename;
            }
        }

        $usuarioModel->update($userId, $updateData);
        // Refrescar la cookie JWT con los datos nuevos
        $updatedUser = $usuarioModel->findCompleto($userId);
        $db = Database::connection();
        $row = $db->prepare(
            'SELECT u.usuario_id, u.correo, u.rol_id, u.foto, u.nombre, u.apellido, u.cedula, r.nombre_rol
             FROM usuarios u JOIN roles_usuarios r ON r.rol_id = u.rol_id
             WHERE u.usuario_id = :id LIMIT 1'
        );
        $row->execute([':id' => $userId]);
        $freshUser = $row->fetch();
        if ($freshUser) {
            Auth::setCookie($freshUser);
        }

        flash('success', 'Perfil actualizado correctamente.');
        return $this->redirect('/admin/perfil');
    }

    public function updateSeguridad(Request $request): Response
    {
        $user = Auth::user();
        $userId = (int) $user['usuario_id'];

        $currentPassword = (string) $request->input('current_password', '');
        $newPassword      = (string) $request->input('new_password', '');
        $confirmPassword  = (string) $request->input('new_password_confirm', '');

        $pregunta1  = $request->input('pregunta_1', '');
        $respuesta1 = $request->input('respuesta_1', '');
        $pregunta2  = $request->input('pregunta_2', '');
        $respuesta2 = $request->input('respuesta_2', '');

        // Verificar contraseña actual
        $db = Database::connection();
        $stmt = $db->prepare('SELECT contrasena FROM usuarios WHERE usuario_id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($currentPassword, (string) $row['contrasena'])) {
            $this->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
            flash('error', 'La contraseña actual es incorrecta.');
            return $this->redirect('/admin/perfil?tab=seguridad');
        }

        // Verificar si se solicitó algún cambio (nueva contraseña o preguntas de seguridad)
        $hasPasswordChange = ($newPassword !== '');
        $hasQuestionsChange = (!empty($pregunta1) || !empty($respuesta1) || !empty($pregunta2) || !empty($respuesta2));

        if (!$hasPasswordChange && !$hasQuestionsChange) {
            $this->withErrors(['new_password' => 'Debe ingresar una nueva contraseña o configurar las preguntas de seguridad.']);
            flash('error', 'No se detectó ningún cambio. Debe rellenar al menos una sección.');
            return $this->redirect('/admin/perfil?tab=seguridad');
        }

        $usuarioModel = new Usuario();

        // Cambio de contraseña
        if ($hasPasswordChange) {
            if (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
                $this->withErrors(['new_password' => 'La nueva contraseña debe tener al menos 8 caracteres, una letra, un número y un símbolo.']);
                return $this->redirect('/admin/perfil?tab=seguridad');
            }
            if ($newPassword !== $confirmPassword) {
                $this->withErrors(['new_password_confirm' => 'Las contraseñas no coinciden.']);
                return $this->redirect('/admin/perfil?tab=seguridad');
            }

            // Validar que no sea igual a la cédula
            $pwdNum = preg_replace('/[^0-9]/', '', $newPassword);
            $cedNum = preg_replace('/[^0-9]/', '', (string)$user['cedula']);

            if ($cedNum !== '' && $pwdNum === $cedNum) {
                $this->withErrors(['new_password' => 'La nueva contraseña no puede ser tu número de cédula.']);
                return $this->redirect('/admin/perfil?tab=seguridad');
            }
            $usuarioModel->update($userId, [
                'contrasena' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12])
            ]);
        }

        // Actualizar preguntas de seguridad
        if ($hasQuestionsChange) {
            if (empty($pregunta1) || empty($respuesta1) || empty($pregunta2) || empty($respuesta2)) {
                $this->withErrors(['pregunta_1' => 'Para configurar las preguntas de seguridad, debe completar las dos preguntas y sus respuestas.']);
                flash('error', 'Debe completar todas las preguntas y respuestas de seguridad.');
                return $this->redirect('/admin/perfil?tab=seguridad');
            }
            if ($pregunta1 === $pregunta2) {
                $this->withErrors(['pregunta_2' => 'Las preguntas deben ser diferentes.']);
                return $this->redirect('/admin/perfil?tab=seguridad');
            }
            if (strlen(trim($respuesta1)) < 3 || strlen(trim($respuesta2)) < 3) {
                $this->withErrors(['respuesta_1' => 'Las respuestas deben tener al menos 3 caracteres.']);
                flash('error', 'Las respuestas de seguridad son muy cortas.');
                return $this->redirect('/admin/perfil?tab=seguridad');
            }

            $respModel = new RespuestaSeguridad();
            $respModel->deleteByUser($userId);
            $respModel->saveRespuesta($userId, (int) $pregunta1, trim($respuesta1));
            $respModel->saveRespuesta($userId, (int) $pregunta2, trim($respuesta2));
        }

        flash('success', 'Seguridad actualizada correctamente.');
        return $this->redirect('/admin/perfil?tab=seguridad');
    }

    /**
     * Muestra la pantalla con las tarjetas de los manuales disponibles.
     */
    public function descargarManual(Request $request): Response
    {
        $user = Auth::user();
        $isSuper = ((int) ($user['rol_id'] ?? 0)) === ROL_SUPERUSER;
        
        $dir = BASE_PATH . '/public/uploads/manuales';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $manuals = [
            'sistema' => [
                'exists' => is_file($dir . '/manual_sistema.pdf'),
                'url'    => url('/uploads/manuales/manual_sistema.pdf'),
                'title'  => 'Manual del Sistema',
                'file'   => 'manual_sistema.pdf',
                'icon'   => 'ph ph-cpu',
                'desc'   => 'Detalles de arquitectura, modelo de base de datos y flujos técnicos del backend para el mantenimiento del sistema.'
            ],
            'instalacion' => [
                'exists' => is_file($dir . '/manual_instalacion.pdf'),
                'url'    => url('/uploads/manuales/manual_instalacion.pdf'),
                'title'  => 'Manual de Instalación',
                'file'   => 'manual_instalacion.pdf',
                'icon'   => 'ph ph-wrench',
                'desc'   => 'Instrucciones paso a paso para el despliegue del sistema en servidores web o en entornos locales.'
            ],
            'usuario' => [
                'exists' => is_file($dir . '/manual_usuario.pdf'),
                'url'    => url('/uploads/manuales/manual_usuario.pdf'),
                'title'  => 'Manual de Usuario',
                'file'   => 'manual_usuario.pdf',
                'icon'   => 'ph ph-users-three',
                'desc'   => 'Guía visual e interactiva para el manejo del perfil, control de asistencias, evaluaciones físicas y fichas médicas.'
            ]
        ];
        
        return $this->view('perfil.manuales', [
            'title'      => 'Manuales de Ayuda',
            'isSuper'    => $isSuper,
            'manuals'    => $manuals,
            'active'     => 'manuales',
            'breadcrumb' => ['Inicio', 'Manuales de Ayuda']
        ], 'admin');
    }
}
