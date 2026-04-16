<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Atleta;
use App\Models\Tutor;
use App\Models\Direccion;
use App\Models\FichaMedica;
use RuntimeException;
use Throwable;

/**
 * Encapsula la creación/actualización de un atleta con sus entidades
 * relacionadas (tutor, dirección, ficha médica) en una única transacción.
 */
final class AtletaService
{
    public function crear(array $data, array $fotoFile = []): int
    {
        Database::beginTransaction();
        try {
            $direccionId = $this->guardarDireccion($data);
            $tutorId     = $this->guardarTutor($data, $direccionId);
            $fotoPath    = $this->guardarFoto($fotoFile);

            $atleta = new Atleta();
            $atletaId = $atleta->insert([
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?? null,
                'fecha_nacimiento'  => $data['fecha_nacimiento'],
                'posicion_de_juego' => $data['posicion_de_juego'] ?? null,
                'pierna_dominante'  => $data['pierna_dominante'] ?? null,
                'categoria_id'      => $data['categoria_id'] ?? null,
                'tutor_id'          => $tutorId,
                'direccion_id'      => $direccionId,
                'foto'              => $fotoPath,
                'estatus'           => $data['estatus'] ?? 'Activo',
            ]);

            $this->guardarFichaMedica($atletaId, $data);

            Database::commit();
            Logger::audit('atleta.crear', ['atleta_id' => $atletaId]);
            return $atletaId;
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $atletaId, array $data, array $fotoFile = []): void
    {
        $atleta = new Atleta();
        $actual = $atleta->find($atletaId);
        if (!$actual) {
            throw new RuntimeException('Atleta no encontrado.');
        }

        Database::beginTransaction();
        try {
            // Dirección: reutilizar existente o crear nueva
            $direccionId = $actual['direccion_id'] ?? null;
            if ($direccionId) {
                (new Direccion())->update((int) $direccionId, [
                    'parroquia_id'    => $data['parroquia_id'] ?? null,
                    'punto_referencia' => $data['punto_referencia'] ?? null,
                    'calle_avenida'   => $data['calle_avenida'] ?? null,
                    'casa_edificio'   => $data['casa_edificio'] ?? null,
                ]);
            } else {
                $direccionId = $this->guardarDireccion($data);
            }

            $tutorId = $this->guardarTutor($data, $direccionId, (int) ($actual['tutor_id'] ?? 0));

            $update = [
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?? null,
                'fecha_nacimiento'  => $data['fecha_nacimiento'],
                'posicion_de_juego' => $data['posicion_de_juego'] ?? null,
                'pierna_dominante'  => $data['pierna_dominante'] ?? null,
                'categoria_id'      => $data['categoria_id'] ?? null,
                'tutor_id'          => $tutorId,
                'direccion_id'      => $direccionId,
                'estatus'           => $data['estatus'] ?? $actual['estatus'],
            ];
            $nuevaFoto = $this->guardarFoto($fotoFile);
            if ($nuevaFoto !== null) {
                $update['foto'] = $nuevaFoto;
            }
            $atleta->update($atletaId, $update);

            $this->guardarFichaMedica($atletaId, $data);

            Database::commit();
            Logger::audit('atleta.actualizar', ['atleta_id' => $atletaId]);
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    private function guardarDireccion(array $data): ?int
    {
        if (empty($data['parroquia_id']) && empty($data['calle_avenida']) && empty($data['casa_edificio'])) {
            return null;
        }
        return (new Direccion())->insert([
            'parroquia_id'    => $data['parroquia_id'] ?? null,
            'punto_referencia' => $data['punto_referencia'] ?? null,
            'calle_avenida'   => $data['calle_avenida'] ?? null,
            'casa_edificio'   => $data['casa_edificio'] ?? null,
        ]);
    }

    private function guardarTutor(array $data, ?int $direccionId, int $tutorIdExistente = 0): ?int
    {
        if (empty($data['tutor_cedula']) && empty($data['tutor_nombres'])) {
            return $tutorIdExistente ?: null;
        }
        $tutorModel = new Tutor();
        $existente = !empty($data['tutor_cedula']) ? $tutorModel->findByCedula($data['tutor_cedula']) : null;
        if ($existente) {
            $tutorModel->update((int) $existente['tutor_id'], [
                'nombres'       => $data['tutor_nombres'] ?? $existente['nombres'],
                'apellidos'     => $data['tutor_apellidos'] ?? $existente['apellidos'],
                'telefono'      => $data['tutor_telefono'] ?? $existente['telefono'],
                'correo'        => $data['tutor_correo'] ?? $existente['correo'],
                'tipo_relacion' => $data['tutor_relacion'] ?? $existente['tipo_relacion'],
                'direccion_id'  => $direccionId ?? $existente['direccion_id'],
            ]);
            return (int) $existente['tutor_id'];
        }
        return $tutorModel->insert([
            'nombres'       => $data['tutor_nombres'] ?? '',
            'apellidos'     => $data['tutor_apellidos'] ?? '',
            'cedula'        => $data['tutor_cedula'] ?? '',
            'telefono'      => $data['tutor_telefono'] ?? '',
            'correo'        => $data['tutor_correo'] ?? null,
            'tipo_relacion' => $data['tutor_relacion'] ?? 'Padre',
            'direccion_id'  => $direccionId,
        ]);
    }

    private function guardarFichaMedica(int $atletaId, array $data): void
    {
        $tieneData = !empty($data['alergias']) || !empty($data['tipo_sanguineo'])
            || !empty($data['lesion']) || !empty($data['condicion_medica'])
            || !empty($data['observacion']);
        if (!$tieneData) return;

        $model = new FichaMedica();
        $actual = $model->byAtleta($atletaId);
        $payload = [
            'alergias'        => $data['alergias'] ?? null,
            'tipo_sanguineo'  => $data['tipo_sanguineo'] ?? null,
            'lesion'          => $data['lesion'] ?? null,
            'condicion_medica' => $data['condicion_medica'] ?? null,
            'observacion'     => $data['observacion'] ?? null,
        ];
        if ($actual) {
            $model->update((int) $actual['ficha_id'], $payload);
        } else {
            $model->insert(['atleta_id' => $atletaId] + $payload);
        }
    }

    private function guardarFoto(array $file): ?string
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir la foto (código ' . $file['error'] . ').');
        }
        $maxSize = (int) config('app.uploads.max_size');
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('La foto excede el tamaño máximo permitido.');
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) finfo_close($finfo);
        $allowed = config('app.uploads.allowed_mime') ?? [];
        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Tipo de archivo no permitido. Usa JPG, PNG o WebP.');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'bin',
        };
        $basename = bin2hex(random_bytes(8)) . '.' . $ext;
        $dir = BASE_PATH . '/public' . config('app.uploads.atletas_dir');
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $dest = $dir . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('No se pudo guardar la foto.');
        }
        return config('app.uploads.atletas_dir') . '/' . $basename;
    }
}
