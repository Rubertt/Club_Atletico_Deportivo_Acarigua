<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Convocatoria;
use App\Models\AsigCategoria;

final class ConvocatoriasApiController extends Controller
{
    public function atletasCategoriaConvocatoria(Request $request): Response
    {
        $categoriaId = (int) $request->param('id');
        
        // 1. Sincronizar dinámicamente los estatus de las asignaciones para esta categoría
        (new AsigCategoria())->assignedAthletes($categoriaId);

        // 2. Buscar atletas con asignación vigente (ac.estatus = 1)
        $stmt = Database::connection()->prepare(
            "SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.foto, a.estatus AS atleta_estatus
             FROM asig_categorias ac
             JOIN atletas a ON a.atleta_id = ac.atleta_id
             WHERE ac.categoria_id = :c AND ac.estatus = 1
             ORDER BY a.apellido, a.nombre"
        );
        $stmt->execute([':c' => $categoriaId]);
        $atletas = $stmt->fetchAll();

        // 3. Agregar métricas a cada atleta
        $convocatoriaModel = new Convocatoria();
        $resultadoPruebaModel = new \App\Models\ResultadoPrueba();
        foreach ($atletas as &$atleta) {
            $atleta['asistencia_mensual'] = $convocatoriaModel->obtenerAsistenciaMensual((int)$atleta['atleta_id']);
            
            // Solicitar al modelo el cálculo y la variable promedio
            $resultadoPruebaModel->calcularPromedioMasReciente((int)$atleta['atleta_id']);
            $atleta['rendimiento_fisico'] = $resultadoPruebaModel->promedio ?? 0.0;
        }
        unset($atleta);

        return $this->json($atletas);
    }
}
