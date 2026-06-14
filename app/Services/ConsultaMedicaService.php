<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\ConsultaMedica;
use App\Models\Atleta;
use Throwable;

final class ConsultaMedicaService
{
    /**
     * Registra una nueva consulta médica y sincroniza el estatus del atleta.
     */
    public function crear(array $data): int
    {
        Database::beginTransaction();
        try {
            $consultaModel = new ConsultaMedica();
            $consultaId = $consultaModel->insert([
                'atleta_id'              => $data['atleta_id'],
                'usuario_id'             => $data['usuario_id'],
                'tipo_consulta'          => $data['tipo_consulta'],
                'diagnostico'            => $data['diagnostico'],
                'descripcion'            => $data['descripcion'] ?: null,
                'tratamiento_indicado'   => $data['tratamiento_indicado'] ?: null,
                'fecha_suceso'           => $data['fecha_suceso'],
                'fecha_alta_estimada'    => $data['fecha_alta_estimada'] ?: null,
                'estatus_disponibilidad' => $data['estatus_disponibilidad'],
                'creado_en'              => $data['creado_en'],
            ]);

            $this->sincronizarEstatusAtleta((int) $data['atleta_id']);

            Database::commit();
            Logger::audit('consulta_medica.crear', ['consulta_id' => $consultaId, 'atleta_id' => $data['atleta_id']]);
            return $consultaId;
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una consulta médica y sincroniza el estatus del atleta.
     */
    public function actualizar(int $consultaId, array $data): void
    {
        Database::beginTransaction();
        try {
            $consultaModel = new ConsultaMedica();
            $consultaModel->update($consultaId, [
                'tipo_consulta'          => $data['tipo_consulta'],
                'diagnostico'            => $data['diagnostico'],
                'descripcion'            => $data['descripcion'] ?: null,
                'tratamiento_indicado'   => $data['tratamiento_indicado'] ?: null,
                'fecha_suceso'           => $data['fecha_suceso'],
                'fecha_alta_estimada'    => $data['fecha_alta_estimada'] ?: null,
                'estatus_disponibilidad' => $data['estatus_disponibilidad'],
            ]);

            $this->sincronizarEstatusAtleta((int) $data['atleta_id']);

            Database::commit();
            Logger::audit('consulta_medica.actualizar', ['consulta_id' => $consultaId, 'atleta_id' => $data['atleta_id']]);
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina una consulta médica.
     */
    public function eliminar(int $consultaId): void
    {
        Database::beginTransaction();
        try {
            $consultaModel = new ConsultaMedica();
            $consulta = $consultaModel->find($consultaId);
            if ($consulta) {
                $atletaId = (int) $consulta['atleta_id'];
                $consultaModel->delete($consultaId);
                $this->sincronizarEstatusAtleta($atletaId);
            }
            Database::commit();
            Logger::audit('consulta_medica.eliminar', ['consulta_id' => $consultaId]);
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Sincroniza el estatus en la tabla `atletas` de acuerdo al registro más reciente
     * en la tabla consultas médicas.
     */
    private function sincronizarEstatusAtleta(int $atletaId): void
    {
        $atletaModel = new Atleta();
        $atleta = $atletaModel->find($atletaId);
        if (!$atleta) {
            return;
        }

        // Obtener el registro más reciente de consulta_medica para este atleta (por creado_en, actualizado_en y id)
        $consultaModel = new ConsultaMedica();
        $masReciente = $consultaModel->queryOne("
            SELECT estatus_disponibilidad 
            FROM `consulta_medica` 
            WHERE atleta_id = :atleta_id 
            ORDER BY creado_en DESC, actualizado_en DESC, consulta_id DESC 
            LIMIT 1
        ", [':atleta_id' => $atletaId]);

        if ($masReciente) {
            $estatusDisponibilidad = (int) $masReciente['estatus_disponibilidad'];
            if ($estatusDisponibilidad === 0) {
                $atletaModel->update($atletaId, ['estatus' => 2]); // Lesionado (No Apto)
            } elseif ($estatusDisponibilidad === 1) {
                $atletaModel->update($atletaId, ['estatus' => 1]); // Activo / Disponible (Apto)
            }
        } else {
            // Si no quedan consultas médicas para este atleta, restablecer a Activo (1)
            $atletaModel->update($atletaId, ['estatus' => 1]);
        }
    }
}
