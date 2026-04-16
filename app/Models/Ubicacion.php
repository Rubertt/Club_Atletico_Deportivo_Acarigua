<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Ubicacion extends Model
{
    protected string $table = 'ubicacion_pais'; // por defecto; cada método selecciona la tabla correcta

    public function paises(): array
    {
        return $this->query('SELECT pais_id, nombre FROM ubicacion_pais ORDER BY nombre');
    }

    public function estados(int $paisId): array
    {
        return $this->query(
            'SELECT estado_id, nombre FROM ubicacion_estado WHERE pais_id = :p ORDER BY nombre',
            [':p' => $paisId]
        );
    }

    public function municipios(int $estadoId): array
    {
        return $this->query(
            'SELECT municipio_id, nombre FROM ubicacion_municipio WHERE estado_id = :e ORDER BY nombre',
            [':e' => $estadoId]
        );
    }

    public function parroquias(int $municipioId): array
    {
        return $this->query(
            'SELECT parroquia_id, nombre FROM ubicacion_parroquia WHERE municipio_id = :m ORDER BY nombre',
            [':m' => $municipioId]
        );
    }
}
