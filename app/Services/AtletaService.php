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
            $direccionId = !empty($data['direccion_id'])
                ? (int) $data['direccion_id']
                : $this->guardarDireccion($data);

            $representanteId = !empty($data['representante_id'])
                ? (int) $data['representante_id']
                : $this->guardarRepresentante($data);

            $fotoPath    = $this->guardarFoto($fotoFile);

            $atleta = new Atleta();
            $atletaId = $atleta->insert([
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'fecha_nac'         => $data['fecha_nacimiento'],
                'sexo'              => $data['sexo'] ?? 'M', // Por default para evitar error
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?: null,
                'pierna_dominante'  => !empty($data['pierna_dominante']) ? $data['pierna_dominante'] : null,
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
            // Dirección: reutilizar existente, asociar nueva, o actualizar existente
            $newDireccionId = !empty($data['direccion_id']) ? (int) $data['direccion_id'] : null;
            $oldDireccionId = $actual['direccion_id'] ? (int) $actual['direccion_id'] : null;

            if ($newDireccionId !== null && $newDireccionId !== $oldDireccionId) {
                $direccionId = $newDireccionId;
                if ($oldDireccionId !== null) {
                    $this->eliminarDireccionSiHuerfana($oldDireccionId);
                }
            } else {
                if ($oldDireccionId !== null) {
                    (new Direccion())->update($oldDireccionId, [
                        'parroquias_id'     => $data['parroquia_id'] ?? null,
                        'localidad'         => $data['localidad'] ?? null,
                        'tipo_vivienda'     => $data['tipo_vivienda'] ?? null,
                        'ubicacion_vivienda'=> $data['ubicacion_vivienda'] ?? null,
                    ]);
                    $direccionId = $oldDireccionId;
                } else {
                    $direccionId = $this->guardarDireccion($data);
                }
            }

            // Representante: reutilizar existente, asociar nuevo, o actualizar existente
            $newRepresentanteId = !empty($data['representante_id']) ? (int) $data['representante_id'] : null;
            $oldRepresentanteId = $actual['representante_id'] ? (int) $actual['representante_id'] : null;

            if ($newRepresentanteId !== null && $newRepresentanteId !== $oldRepresentanteId) {
                $representanteId = $newRepresentanteId;
                if ($oldRepresentanteId !== null) {
                    $this->eliminarRepresentanteSiHuerfano($oldRepresentanteId);
                }
            } else {
                $useOldId = !empty($data['crear_nuevo_representante']) ? 0 : ($oldRepresentanteId ?: 0);
                $representanteId = $this->guardarRepresentante($data, $useOldId);
                if ($oldRepresentanteId !== null && $representanteId !== $oldRepresentanteId) {
                    $this->eliminarRepresentanteSiHuerfano($oldRepresentanteId);
                }
            }

            $update = [
                'nombre'            => $data['nombre'],
                'apellido'          => $data['apellido'],
                'fecha_nac'         => $data['fecha_nacimiento'],
                'sexo'              => $data['sexo'] ?? $actual['sexo'],
                'cedula'            => $data['cedula'] ?: null,
                'telefono'          => $data['telefono'] ?: null,
                'pierna_dominante'  => !empty($data['pierna_dominante']) ? $data['pierna_dominante'] : null,
                'representante_id'  => $representanteId,
                'direccion_id'      => $direccionId,
                'estatus'           => $data['estatus'] ?? $actual['estatus'],
            ];
            
            if (!empty($data['eliminar_foto'])) {
                $update['foto'] = null;
            } else {
                $nuevaFoto = $this->guardarFoto($fotoFile);
                if ($nuevaFoto !== null) {
                    $update['foto'] = $nuevaFoto;
                }
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

    private function guardarRepresentante(array $data, int $representanteIdExistente = 0): int
    {
        $representanteModel = new Representante(); // Apunta a representantes
        
        $nuevaCedula = !empty($data['tutor_cedula']) ? $data['tutor_cedula'] : 'S/N';

        // 1. Buscar si ya existe un representante con este nuevo Documento ID (independiente de si es registro o edición)
        $existente = ($nuevaCedula !== 'S/N') ? $representanteModel->findByCedula($nuevaCedula) : null;

        if ($existente) {
            // Si ya existe, actualizamos sus datos para mantenerlo al día
            $representanteModel->update((int) $existente['representante_id'], [
                'nombre'        => $data['tutor_nombres'] ?? $existente['nombre'],
                'apellido'      => $data['tutor_apellidos'] ?? $existente['apellido'],
                'telefono'      => $data['tutor_telefono'] ?? $existente['telefono'],
                'tipo_relacion' => $data['tutor_relacion'] ?? $existente['tipo_relacion'],
            ]);
            
            $nuevoId = (int) $existente['representante_id'];

            // Si es una edición y el atleta tenía un representante previo asignado que ahora es diferente
            // al que encontramos por Documento ID, comprobamos si el anterior quedó huérfano para eliminarlo.
            if ($representanteIdExistente > 0 && $representanteIdExistente !== $nuevoId) {
                $this->eliminarRepresentanteSiHuerfano($representanteIdExistente);
            }

            return $nuevoId;
        }

        // 2. Si no existe ningún representante con ese Documento ID en la BD:
        // Si tenemos un ID existente de representante asignado a este atleta, simplemente actualizamos ese registro.
        if ($representanteIdExistente > 0) {
            $previo = $representanteModel->find($representanteIdExistente);
            if ($previo) {
                $representanteModel->update($representanteIdExistente, [
                    'nombre'        => $data['tutor_nombres'] ?? $previo['nombre'],
                    'apellido'      => $data['tutor_apellidos'] ?? $previo['apellido'],
                    'cedula'        => $nuevaCedula,
                    'telefono'      => $data['tutor_telefono'] ?? $previo['telefono'],
                    'tipo_relacion' => $data['tutor_relacion'] ?? $previo['tipo_relacion'],
                ]);
                return $representanteIdExistente;
            }
        }

        // 3. Si no existe de ninguna forma y es un nuevo atleta, registramos uno nuevo
        return $representanteModel->insert([
            'nombre'        => $data['tutor_nombres'] ?? 'Sin Nombre',
            'apellido'      => $data['tutor_apellidos'] ?? '',
            'cedula'        => $nuevaCedula,
            'telefono'      => $data['tutor_telefono'] ?? '',
            'tipo_relacion' => $data['tutor_relacion'] ?? 'representante',
        ]);
    }

    /**
     * Elimina un representante de la base de datos si ningún atleta está vinculado a él.
     */
    private function eliminarRepresentanteSiHuerfano(int $representanteId): void
    {
        try {
            $db = Database::connection();
            // Verificar si hay atletas vinculados
            $stmt = $db->prepare('SELECT COUNT(*) FROM atletas WHERE representante_id = ?');
            $stmt->execute([$representanteId]);
            $count = (int) $stmt->fetchColumn();
            
            if ($count === 0) {
                $db->prepare('DELETE FROM representantes WHERE representante_id = ?')->execute([$representanteId]);
            }
        } catch (Throwable $e) {
            Logger::error($e);
        }
    }

    /**
     * Elimina una dirección de la base de datos si ningún atleta está vinculado a ella.
     */
    private function eliminarDireccionSiHuerfana(int $direccionId): void
    {
        try {
            $db = Database::connection();
            
            // Verificar si hay atletas vinculados a esta dirección
            $stmt1 = $db->prepare('SELECT COUNT(*) FROM atletas WHERE direccion_id = ?');
            $stmt1->execute([$direccionId]);
            $count1 = (int) $stmt1->fetchColumn();
            
            if ($count1 === 0) {
                $db->prepare('DELETE FROM direcciones WHERE direccion_id = ?')->execute([$direccionId]);
            }
        } catch (Throwable $e) {
            Logger::error($e);
        }
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

        // Optimizar y redimensionar la imagen si excede el tamaño ideal
        $this->optimizarImagen($dest, $mime);

        return config('app.uploads.atletas_dir') . '/' . $basename;
    }

    /**
     * Redimensiona y optimiza una imagen subida para ahorrar espacio, ancho de banda
     * y mejorar los tiempos de renderizado en el navegador del cliente.
     */
    private function optimizarImagen(string $filePath, string $mime): void
    {
        try {
            $info = getimagesize($filePath);
            if (!$info) return;

            list($width, $height) = $info;
            $maxDim = 600; // Resolución ideal óptima para fotos de perfil 180x180

            // Si es más pequeña que el límite, no hace falta redimensionarla
            if ($width <= $maxDim && $height <= $maxDim) {
                return;
            }

            // Calcular proporciones manteniendo relación de aspecto
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxDim;
                $newHeight = (int) round($maxDim / $ratio);
            } else {
                $newHeight = $maxDim;
                $newWidth = (int) round($maxDim * $ratio);
            }

            // Crear el recurso de imagen de origen según el tipo MIME
            $srcImage = null;
            if (extension_loaded('gd')) {
                $srcImage = match ($mime) {
                    'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($filePath) : null,
                    'image/png'  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($filePath) : null,
                    'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : null,
                    default      => null
                };
            }

            if (!$srcImage) return;

            // Crear el lienzo de destino
            if (!function_exists('imagecreatetruecolor')) {
                imagedestroy($srcImage);
                return;
            }
            $dstImage = imagecreatetruecolor($newWidth, $newHeight);
            if (!$dstImage) {
                imagedestroy($srcImage);
                return;
            }

            // Manejo y preservación de transparencias para PNG y WebP
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Redimensionar con interpolación bilineal de alta calidad
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Guardar sobreescribiendo el archivo original con compresión óptima
            match ($mime) {
                'image/jpeg' => imagejpeg($dstImage, $filePath, 85),
                'image/png'  => imagepng($dstImage, $filePath, 6),
                'image/webp' => imagewebp($dstImage, $filePath, 80),
                default      => null
            };

            // Liberar memoria
            imagedestroy($srcImage);
            imagedestroy($dstImage);
        } catch (Throwable $e) {
            // Registramos el error de GD pero no bloqueamos el flujo de guardado principal
            Logger::error($e);
        }
    }
}
