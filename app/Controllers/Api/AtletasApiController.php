<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Atleta;

final class AtletasApiController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = array_filter([
            'categoria_id' => $request->query('categoria_id'),
            'estatus'      => $request->query('estatus'),
            'q'            => $request->query('q'),
        ], fn($v) => $v !== null && $v !== '');

        $data = (new Atleta())->paginate(
            $filters,
            (int) $request->query('page', 1),
            (int) $request->query('per_page', 20)
        );
        return $this->json($data);
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $atleta = (new Atleta())->findCompleto($id);
        if (!$atleta) {
            return $this->json(['error' => 'No encontrado'], 404);
        }
        return $this->json($atleta);
    }
}
