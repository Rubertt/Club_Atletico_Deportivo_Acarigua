<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Atleta;
use App\Models\Representante;
use App\Models\Direccion;
use App\Models\FichaMedica;
use RuntimeException;
use Throwable;

/**
 * Encapsula la creación/actualización de un atleta con sus entidades
 * relacionadas (representante, dirección, ficha médica) en una única transacción.
 */
final class AtletaService
{
    public function crear(array $data, array $fotoFile = []): int
    {
        Database::beginTransaction();
        try {
            $direccionId = $this->guardarDireccion($data);
            $representanteId = $this->guardarRepresentante($data, $direccionId);
            $fotoPath    = $this->guardarFoto($fotoFile);

            $atleta = new Atleta();
            $atletaId = $atleta->insert([
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'fecha_nacimiento'  => $data['fecha_nacimiento'],
                'sexo'              => $data['sexo'] ?? 'M', // Por default para evitar error
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?? null,
                'posicion_de_juego' => $data['posicion_de_juego'] ?? null,
                'pierna_dominante'  => $data['pierna_dominante'] ?? null,
                'categoria_id'      => $data['categoria_id'] ?? null,
                'representante_id'  => $representanteId,
                'direccion_id'      => $direccionId,
                'foto'              => $fotoPath,
                'estatus'           => $data['estatus'] ?? 1, // 1: Activo
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
                    'parroquias_id'     => $data['parroquia_id'] ?? null,
                    'localidad'         => $data['localidad'] ?? null,
                    'tipo_vivienda'     => $data['tipo_vivienda'] ?? null,
                    'ubicacion_vivienda'=> $data['ubicacion_vivienda'] ?? null,
                ]);
            } else {
                $direccionId = $this->guardarDireccion($data);
            }

            $representanteId = $this->guardarRepresentante($data, $direccionId, (int) ($actual['representante_id'] ?? 0));

            $update = [
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'fecha_nacimiento'  => $data['fecha_nacimiento'],
                'sexo'              => $data['sexo'] ?? $actual['sexo'],
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?? null,
                'posicion_de_juego' => $data['posicion_de_juego'] ?? null,
                'pierna_dominante'  => $data['pierna_dominante'] ?? null,
                'categoria_id'      => $data['categoria_id'] ?? null,
                'representante_id'  => $representanteId,
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

    private function guardarDireccion(array $data): int
    {
        return (new Direccion())->insert([
            'parroquias_id'     => $data['parroquia_id'] ?? 1, // Fix temporal si es requerido
            'localidad'         => $data['localidad'] ?? '',
            'tipo_vivienda'     => $data['tipo_vivienda'] ?? 'casa',
            'ubicacion_vivienda'=> $data['ubicacion_vivienda'] ?? '',
        ]);
    }

    private function guardarRepresentante(array $data, int $direccionId, int $representanteIdExistente = 0): int
    {
        $representanteModel = new Representante(); // Apunta a representante
        $existente = !empty($data['tutor_cedula']) ? $representanteModel->findByCedula($data['tutor_cedula']) : null;
        
        $nombreCompleto = !empty($data['tutor_nombres']) ? ($data['tutor_nombres'] . ' ' . ($data['tutor_apellidos'] ?? '')) : 'Sin Nombre';

        if ($existente) {
            $representanteModel->update((int) $existente['representante_id'], [
                'nombre_completo' => $nombreCompleto,
                'telefono'        => $data['tutor_telefono'] ?? $existente['telefono'],
                'tipo_relacion'   => $data['tutor_relacion'] ?? $existente['tipo_relacion'],
                'direccion_id'    => $direccionId,
            ]);
            return (int) $existente['representante_id'];
        }
        return $representanteModel->insert([
            'nombre_completo' => $nombreCompleto,
            'cedula'          => $data['tutor_cedula'] ?? 'S/N',
            'telefono'        => $data['tutor_telefono'] ?? '',
            'tipo_relacion'   => $data['tutor_relacion'] ?? 'representante',
            'direccion_id'    => $direccionId,
        ]);
    }

    private function guardarFichaMedica(int $atletaId, array $data): void
    {
        $tieneData = !empty($data['alergias']) || !empty($data['grupo_sanguineo'])
            || !empty($data['condicion_cronica']) || !empty($data['antecedentes_quirurgicos']);
        if (!$tieneData) return;

        $model = new FichaMedica();
        $actual = $model->byAtleta($atletaId);
        $payload = [
            'grupo_sanguineo'          => $data['grupo_sanguineo'] ?? 'O+',
            'alergias'                 => $data['alergias'] ?? null,
            'antecedentes_familiares'  => $data['antecedentes_familiares'] ?? null,
            'antecedentes_quirurgicos' => $data['antecedentes_quirurgicos'] ?? null,
            'condicion_cronica'        => $data['condicion_cronica'] ?? null,
            'medicacion_actual'        => $data['medicacion_actual'] ?? null,
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
