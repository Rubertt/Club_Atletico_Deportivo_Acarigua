<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;
use App\Models\EventoDeportivo;
use Throwable;

final class AsistenciaService
{
    /**
     * Registra un evento (entrenamiento/pruebas/etc.) y bulk insert de asistencias.
     *
     * @param array<int, array{atleta_id:int, estatus:string, observaciones?:string}> $detalles
     */
    public function registrarPase(
        int $entrenadorId,
        string $tipoEvento,
        string $fechaEvento,
        array $detalles
    ): int {
        if (empty($detalles)) {
            throw new \RuntimeException('Debes marcar la asistencia de al menos un atleta.');
        }

        Database::beginTransaction();
        try {
            $eventoId = (new EventoDeportivo())->insert([
                'entrenador_id' => $entrenadorId,
                'tipo_evento'   => $tipoEvento,
                'fecha_evento'  => $fechaEvento,
            ]);

            $stmt = Database::connection()->prepare(
                'INSERT INTO detalle_asistencia (evento_id, atleta_id, estatus, observaciones)
                 VALUES (:e, :a, :s, :o)'
            );
            foreach ($detalles as $d) {
                $stmt->execute([
                    ':e' => $eventoId,
                    ':a' => (int) $d['atleta_id'],
                    ':s' => $d['estatus'] ?? 'Ausente',
                    ':o' => $d['observaciones'] ?? null,
                ]);
            }

            Database::commit();
            Logger::audit('asistencia.pase', ['evento_id' => $eventoId, 'total' => count($detalles)]);
            return $eventoId;
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
