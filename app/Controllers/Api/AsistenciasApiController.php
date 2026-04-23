<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class AsistenciasApiController extends Controller
{
    public function atletasCategoria(Request $request): Response
    {
        $categoriaId = (int) $request->param('id');
        $stmt = Database::connection()->prepare(
            "SELECT atleta_id, nombre, apellido, cedula, foto
             FROM atletas
             WHERE categoria_id = :c AND estatus = 'Activo'
             ORDER BY apellido, nombre"
        );
        $stmt->execute([':c' => $categoriaId]);
        return $this->json($stmt->fetchAll());
    }
}
